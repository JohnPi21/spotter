<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PipelineTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/spotacus-pipeline-'.bin2hex(random_bytes(8));
        mkdir($this->directory.'/bin', 0700, true);
        mkdir($this->directory.'/deploy');
        mkdir($this->directory.'/docker');
        file_put_contents($this->directory.'/docker/.env.prod', "DB_DATABASE=production\nAPP_IMAGE_REF=old-image\n");
        mkdir($this->directory.'/.git/releases', 0700, true);
        foreach (['post_deploy.sh', 'rollback.sh'] as $script) {
            copy(dirname(__DIR__, 2).'/deploy/'.$script, $this->directory.'/deploy/'.$script);
        }
        $stub = <<<'BASH'
#!/usr/bin/env bash
set -eu
command_name=$(basename "$0")
printf '%s %s image=%s\n' "$command_name" "$*" "${APP_IMAGE_REF:-}" >> "$COMMAND_LOG"
if [[ -n "${FAIL_MATCH:-}" && "$*" == *"$FAIL_MATCH"* ]]; then exit 17; fi
if [[ "$command_name" == git && "$*" == 'rev-parse HEAD' ]]; then echo "$RELEASE_COMMIT"; fi
if [[ "$command_name" == docker && "$*" == *'ps --status running -q app' ]]; then echo running-app; fi
BASH;
        foreach (['docker', 'npm', 'npx', 'curl', 'git'] as $command) {
            file_put_contents($this->directory.'/bin/'.$command, $stub);
            chmod($this->directory.'/bin/'.$command, 0700);
        }
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validationFailures(): array
    {
        return [
            'dependency installation' => ['app composer install'],
            'stack startup' => ['180 runtime nginx'],
            'frontend lint' => ['eslint'],
            'frontend types' => ['vue-tsc'],
            'database preparation' => ['artisan migrate:fresh'],
            'PHP checks' => ['app composer check'],
        ];
    }

    #[DataProvider('validationFailures')]
    public function test_validation_failure_prevents_smoke_checks_and_cleans_up(string $failure): void
    {
        $process = $this->runScript(dirname(__DIR__, 2).'/scripts/ci.sh', ['FAIL_MATCH' => $failure]);
        $this->assertSame(17, $process->getExitCode(), $process->getErrorOutput());
        $this->assertStringNotContainsString('curl ', $this->commands());
        $this->assertStringContainsString('logs --no-color --tail=100', $this->commands());
        $this->assertStringContainsString('down --volumes --remove-orphans', $this->commands());
    }

    public function test_database_preparation_failure_prevents_php_checks(): void
    {
        $process = $this->runScript(dirname(__DIR__, 2).'/scripts/ci.sh', ['FAIL_MATCH' => 'artisan migrate:fresh']);
        $this->assertSame(17, $process->getExitCode());
        $this->assertStringNotContainsString('app composer check', $this->commands());
        $this->assertStringContainsString('down --volumes --remove-orphans', $this->commands());
    }

    public function test_successful_validation_prepares_database_runs_shared_php_checks_and_checks_runtime(): void
    {
        $process = $this->runScript(dirname(__DIR__, 2).'/scripts/ci.sh');
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $commands = $this->commands();
        $this->assertLessThan(strpos($commands, 'app composer check'), strpos($commands, 'artisan migrate:fresh'));
        $this->assertLessThan(strpos($commands, 'artisan migrate:fresh'), strpos($commands, '180 runtime nginx'));
        $this->assertStringContainsString('run --rm app composer install', $commands);
        $this->assertStringNotContainsString('--no-deps', $commands);
        $this->assertSame(1, substr_count($commands, 'app composer check'));
        $this->assertStringContainsString('180 runtime nginx', $commands);
        $this->assertStringContainsString('http://127.0.0.1:8080/health', $commands);
        $this->assertStringContainsString('/login', $commands);
        $this->assertStringContainsString('public/build/manifest.json', $commands);
        $this->assertStringContainsString('down --volumes --remove-orphans', $commands);
    }

    public function test_failed_migration_keeps_successful_release_and_never_replaces_assets(): void
    {
        file_put_contents($this->directory.'/.git/releases/current.env', 'old-release');
        $process = $this->runScript($this->directory.'/deploy/post_deploy.sh', ['FAIL_MATCH' => 'artisan migrate']);
        $this->assertSame(17, $process->getExitCode());
        $this->assertSame('old-release', file_get_contents($this->directory.'/.git/releases/current.env'));
        $this->assertStringNotContainsString('volume rm', $this->commands());
        $this->assertStringNotContainsString('artisan up', $this->commands());
    }

    public function test_failed_smoke_test_restores_maintenance_and_keeps_release_record(): void
    {
        file_put_contents($this->directory.'/.git/releases/current.env', 'old-release');
        $process = $this->runScript($this->directory.'/deploy/post_deploy.sh', ['FAIL_MATCH' => 'https://spotacus.app/up']);
        $this->assertSame(17, $process->getExitCode());
        $this->assertSame('old-release', file_get_contents($this->directory.'/.git/releases/current.env'));
        $this->assertStringContainsString('artisan down', substr($this->commands(), strpos($this->commands(), 'curl ')));
        $this->assertStringNotContainsString('--force-recreate', $this->commands());
    }

    public function test_successful_deployment_records_digest_and_retains_previous_release(): void
    {
        file_put_contents($this->directory.'/.git/releases/current.env', 'old-release');
        $process = $this->runScript($this->directory.'/deploy/post_deploy.sh');
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertSame('old-release', file_get_contents($this->directory.'/.git/releases/previous.env'));
        $this->assertStringContainsString($this->imageReference(), file_get_contents($this->directory.'/.git/releases/current.env'));
        $this->assertSame("DB_DATABASE=production\nAPP_IMAGE_REF=".$this->imageReference()."\n", file_get_contents($this->directory.'/docker/.env.prod'));
        $commands = $this->commands();
        $this->assertLessThan(strpos($commands, 'artisan migrate'), strpos($commands, 'artisan down'));
        $this->assertLessThan(strpos($commands, '--force-recreate'), strpos($commands, '/login'));
        $this->assertStringContainsString('volume rm spotacus_public-build', $commands);
    }

    public function test_mutable_image_tag_is_rejected_before_deployment(): void
    {
        $process = $this->runScript($this->directory.'/deploy/post_deploy.sh', ['APP_IMAGE_REF' => 'ghcr.io/johnpi21/spotter:v1']);
        $this->assertFalse($process->isSuccessful());
        $this->assertFileDoesNotExist($this->directory.'/commands.log');
    }

    public function test_rollback_requires_schema_compatibility_confirmation(): void
    {
        $process = $this->runScript($this->directory.'/deploy/rollback.sh');
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('SCHEMA_COMPATIBLE=true', $process->getErrorOutput());
        $this->assertFileDoesNotExist($this->directory.'/commands.log');
    }

    public function test_rollback_uses_previous_digest_without_migrations(): void
    {
        file_put_contents($this->directory.'/.git/releases/previous.env',
            'APP_IMAGE_REF='.$this->imageReference()."\nRELEASE_COMMIT=".str_repeat('b', 40)."\nRELEASE_TAG=v1\n",
        );
        $process = $this->runScript($this->directory.'/deploy/rollback.sh', ['SCHEMA_COMPATIBLE' => 'true']);
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertStringContainsString('checkout --detach '.str_repeat('b', 40), $this->commands());
        $this->assertStringNotContainsString('artisan migrate', $this->commands());
    }

    public function test_rollback_after_failed_deployment_uses_last_successful_release(): void
    {
        file_put_contents($this->directory.'/.git/releases/current.env',
            'APP_IMAGE_REF='.$this->imageReference()."\nRELEASE_COMMIT=".str_repeat('c', 40)."\nRELEASE_TAG=v2\n",
        );
        file_put_contents($this->directory.'/.git/releases/history.log', "v3 failed\n");
        $process = $this->runScript($this->directory.'/deploy/rollback.sh', ['SCHEMA_COMPATIBLE' => 'true']);
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertStringContainsString('checkout --detach '.str_repeat('c', 40), $this->commands());
        $this->assertStringNotContainsString('artisan migrate', $this->commands());
    }

    public function test_pre_push_hook_does_not_run_checks(): void
    {
        $process = $this->runScript(dirname(__DIR__, 2).'/scripts/.githooks/pre-push');
        $this->assertTrue($process->isSuccessful());
        $this->assertFileDoesNotExist($this->directory.'/commands.log');
    }

    public function test_release_guard_accepts_main_commit_and_rejects_unmerged_branch(): void
    {
        $this->git(['init', '--initial-branch=main']);
        $this->git(['-c', 'user.name=CI', '-c', 'user.email=ci@example.test', '-c', 'commit.gpgsign=false', 'commit', '--allow-empty', '-m', 'Main']);
        $this->git(['update-ref', 'refs/remotes/origin/main', 'HEAD']);
        $process = $this->verifyRelease();
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $this->git(['checkout', '-b', 'unfinished']);
        $this->git(['-c', 'user.name=CI', '-c', 'user.email=ci@example.test', '-c', 'commit.gpgsign=false', 'commit', '--allow-empty', '-m', 'Unmerged']);
        $process = $this->verifyRelease();
        $this->assertFalse($process->isSuccessful());
        $this->assertSame('', $process->getOutput());
    }

    /**
     * @param  array<int, string>  $arguments
     */
    private function git(array $arguments): void
    {
        $process = new Process(array_merge(['/usr/bin/git'], $arguments), $this->directory);
        $process->mustRun();
    }

    private function verifyRelease(): Process
    {
        $process = new Process(['bash', dirname(__DIR__, 2).'/scripts/verify-release.sh'], $this->directory, [
            'PATH' => '/usr/bin:/bin',
            'RELEASE_TAG' => 'v1.0.0',
        ]);
        $process->run();

        return $process;
    }

    /**
     * @param  array<string, string>  $environment
     */
    private function runScript(string $script, array $environment = []): Process
    {
        $process = new Process(['bash', $script], $this->directory, array_merge([
            'PATH' => $this->directory.'/bin:'.getenv('PATH'),
            'COMMAND_LOG' => $this->directory.'/commands.log',
            'APP_IMAGE_REF' => $this->imageReference(),
            'RELEASE_COMMIT' => str_repeat('a', 40),
            'RELEASE_TAG' => 'v2',
            'COMPOSE_PROJECT_NAME' => 'ci-test',
            'FAIL_MATCH' => '',
            'SCHEMA_COMPATIBLE' => 'false',
            'SKIP_MIGRATIONS' => 'false',
        ], $environment));
        $process->run();

        return $process;
    }

    public function test_deployment_passes_credentials_through_stdin_and_sends_remote_script(): void
    {
        $process = $this->runDeployment();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $commands = $this->commands();
        $this->assertStringContainsString('StrictHostKeyChecking=yes', $commands);
        $this->assertStringContainsString('UserKnownHostsFile=', $commands);
        $this->assertStringContainsString('docker login ghcr.io', $commands);
        $this->assertStringNotContainsString('test-registry-token', $commands);
        $this->assertSame("test-registry-token\n", file_get_contents($this->directory.'/login-input'));
        $remoteScript = file_get_contents($this->directory.'/remote-script');
        $this->assertStringContainsString('flock -n 9', $remoteScript);
        $this->assertStringContainsString('git checkout --detach "$1"', $remoteScript);
        $this->assertStringContainsString('bash deploy/post_deploy.sh', $remoteScript);
        $this->assertStringContainsString($this->imageReference(), file_get_contents($this->directory.'/summary'));
        $this->assertSame([], glob($this->directory.'/spotacus-ssh.*'));
    }

    public function test_deployment_login_failure_cleans_credentials_and_prevents_remote_deployment(): void
    {
        $process = $this->runDeployment(['FAIL_MATCH' => 'docker login']);

        $this->assertSame(17, $process->getExitCode());
        $this->assertFileDoesNotExist($this->directory.'/remote-script');
        $this->assertFileDoesNotExist($this->directory.'/summary');
        $this->assertSame([], glob($this->directory.'/spotacus-ssh.*'));
    }

    public function test_deployment_rejects_invalid_release_values_before_connecting(): void
    {
        $process = $this->runDeployment(['RELEASE_TAG' => 'main']);

        $this->assertFalse($process->isSuccessful());
        $this->assertFileDoesNotExist($this->directory.'/commands.log');
        $this->assertSame([], glob($this->directory.'/spotacus-ssh.*'));
    }

    /**
     * @param  array<string, string>  $environment
     */
    private function runDeployment(array $environment = []): Process
    {
        $stub = <<<'BASH'
#!/usr/bin/env bash
set -eu
printf '%s\n' "$*" >> "$COMMAND_LOG"
if [[ -n "${FAIL_MATCH:-}" && "$*" == *"$FAIL_MATCH"* ]]; then exit 17; fi
if [[ "$*" == *'docker login'* ]]; then
    cat > "$RUNNER_TEMP/login-input"
else
    cat > "$RUNNER_TEMP/remote-script"
fi
BASH;
        file_put_contents($this->directory.'/bin/ssh', $stub);
        chmod($this->directory.'/bin/ssh', 0700);

        return $this->runScript(dirname(__DIR__, 2).'/scripts/deploy.sh', array_merge([
            'RUNNER_TEMP' => $this->directory,
            'GITHUB_STEP_SUMMARY' => $this->directory.'/summary',
            'EC2_HOST' => 'example.test',
            'EC2_USER' => 'deployer',
            'EC2_SSH_KEY' => 'test-private-key',
            'EC2_HOST_KEY' => 'example.test ssh-ed25519 test-host-key',
            'GHCR_USERNAME' => 'test-user',
            'GHCR_TOKEN' => 'test-registry-token',
        ], $environment));
    }

    private function imageReference(): string
    {
        return 'ghcr.io/johnpi21/spotter@sha256:'.str_repeat('a', 64);
    }

    private function commands(): string
    {
        return file_get_contents($this->directory.'/commands.log');
    }
}
