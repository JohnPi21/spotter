<?php

namespace App\Console\Commands;

use App\Models\Mesocycle;
use App\Services\MesocyclePlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BackfillMesocyclePlans extends Command
{
    protected $signature = 'mesocycles:backfill-plans
                            {--dry-run : Report changes without saving them}
                            {--user= : Only process mesocycles belonging to this user ID}';

    protected $description = 'Populate missing mesocycle plans and planned statistics without changing templates or workouts';

    public function handle(MesocyclePlan $plan): int
    {
        $userId = $this->option('user');

        if ($userId !== null && (! ctype_digit((string) $userId) || (int) $userId < 1)) {
            $this->error('The --user option must be a positive user ID.');

            return self::INVALID;
        }

        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $failed = 0;
        $query = Mesocycle::query()
            ->where(fn ($query) => $query->whereNull('schema')->orWhereNull('planned_stats'))
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId));

        foreach ($query->lazyById(100) as $mesocycle) {
            try {
                $process = function () use ($mesocycle, $plan, $dryRun): ?array {
                    $current = Mesocycle::query()->when(! $dryRun, fn ($query) => $query->lockForUpdate())->findOrFail($mesocycle->id);

                    if ($current->schema !== null && $current->planned_stats !== null) {
                        return null;
                    }

                    $resolved = $plan->resolve($current);
                    $stats = $plan->stats($resolved['schema']);

                    if (! $dryRun) {
                        if ($current->schema === null) {
                            $current->schema = $resolved['schema'];
                        }

                        $current->planned_stats = $stats;
                        $current->timestamps = false;
                        $current->saveQuietly();
                    }

                    return $resolved;
                };

                $resolved = $dryRun ? $process() : DB::transaction($process);

                if ($resolved === null) {
                    continue;
                }

                $updated++;
                $source = $resolved['source'];
                $source .= $resolved['week'] !== null ? " week {$resolved['week']} (reconstructed)" : '';
                $this->line(($dryRun ? 'Would populate' : 'Populated')." mesocycle {$mesocycle->id} from {$source}.");
            } catch (ValidationException $exception) {
                $failed++;
                $this->warn("Skipped mesocycle {$mesocycle->id}: ".collect($exception->errors())->flatten()->first());
            }
        }

        $this->info(($dryRun ? 'Dry run: ' : '')."{$updated} mesocycles ".($dryRun ? 'would be populated' : 'populated')."; {$failed} skipped as unusable.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
