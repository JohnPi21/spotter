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
"${DC[@]}" run --rm app composer install --no-interaction --prefer-dist --no-progress

echo "Starting the validation stack"
"${DC[@]}" up -d --wait --wait-timeout 180 runtime nginx

echo "Copying Ziggy from the built image for runner-side TypeScript checks"
# TypeScript runs on the runner, but its Ziggy imports point into Composer's vendor directory inside Docker.
# Copy Ziggy to bridge those filesystems. Long term, run frontend checks in a tooling container with
# Node and Composer dependencies together, so checks do not depend on copying vendor files to the runner.
mkdir -p "$REPO_ROOT/vendor/tightenco/ziggy"
"${DC[@]}" cp runtime:/var/www/html/vendor/tightenco/ziggy/. "$REPO_ROOT/vendor/tightenco/ziggy/"

echo "Checking frontend code quality"
npx --no-install eslint resources/js --max-warnings=0
npx --no-install vue-tsc --noEmit

echo "Preparing the isolated test database and checking PHP"
"${DC[@]}" run --rm app php artisan migrate:fresh --force --no-interaction
"${DC[@]}" run --rm app composer check

echo "Checking the image with its original production dependencies"
SMOKE_URL="http://127.0.0.1:${CI_HTTP_PORT:-8080}"
curl --fail --silent --show-error --retry 10 --retry-connrefused --retry-delay 2 --max-time 15 "$SMOKE_URL/health" > /dev/null
curl --fail --silent --show-error "$SMOKE_URL/login" > /dev/null
"${DC[@]}" exec -T runtime sh -c 'test -s public/build/manifest.json'
