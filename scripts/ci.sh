#!/usr/bin/env bash
set -Eeuo pipefail

: "${APP_IMAGE_REF:?Build the application image before running validation}"
: "${COMPOSE_PROJECT_NAME:?Use an isolated Compose project for validation}"

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$REPO_ROOT"
DC=(docker compose -f docker/ci/docker-compose.ci.yml)

cleanup() {
    local status=$?
    trap - EXIT
    if (( status != 0 )); then
        "${DC[@]}" logs --no-color --tail=100 || true
    fi
    "${DC[@]}" down --volumes --remove-orphans || {
        if (( status == 0 )); then status=1; fi
    }
    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

echo "Installing test tooling after the image build"
npm ci --no-audit --no-fund
"${DC[@]}" run --rm --no-deps app composer install --no-interaction --prefer-dist --no-progress

echo "Checking code quality without database services"
npx --no-install eslint resources/js --max-warnings=0
npx --no-install vue-tsc --noEmit
"${DC[@]}" run --rm --no-deps app vendor/bin/pint --test
"${DC[@]}" run --rm --no-deps app vendor/bin/phpstan analyse --memory-limit=2G --no-progress

echo "Running unit tests without database services"
"${DC[@]}" run --rm --no-deps app vendor/bin/phpunit --configuration phpunit.xml --testsuite=Unit

echo "Starting the isolated test database after quality and unit checks pass"
"${DC[@]}" up -d --wait --wait-timeout 180 mysql
"${DC[@]}" run --rm --no-deps app php artisan migrate:fresh --force --no-interaction
"${DC[@]}" run --rm --no-deps app vendor/bin/pest --configuration phpunit.xml --testsuite=Feature

echo "Checking the image with its original production dependencies"
"${DC[@]}" up -d --wait --wait-timeout 180 runtime nginx
SMOKE_URL="http://127.0.0.1:${CI_HTTP_PORT:-8080}"
curl --fail --silent --show-error --retry 10 --retry-connrefused --retry-delay 2 --max-time 15 "$SMOKE_URL/up" > /dev/null
curl --fail --silent --show-error "$SMOKE_URL/login" > /dev/null
"${DC[@]}" exec -T runtime sh -c 'test -s public/build/manifest.json'
