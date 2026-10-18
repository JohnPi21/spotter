#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

: "${APP_IMAGE_REF:?Supply the tested image digest}"
: "${RELEASE_COMMIT:?Supply the matching Git commit}"
: "${RELEASE_TAG:?Supply the release name}"
[[ "$APP_IMAGE_REF" =~ ^ghcr.io/[a-z0-9_./-]+@sha256:[a-f0-9]{64}$ ]]
[[ "$RELEASE_COMMIT" =~ ^[a-f0-9]{40}$ ]]
[[ "$RELEASE_TAG" =~ ^[A-Za-z0-9_.-]+$ ]]

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$REPO_ROOT"
test "$(git rev-parse HEAD)" = "$RELEASE_COMMIT"
STATE_DIR="$REPO_ROOT/.git/releases"
mkdir -p "$STATE_DIR"
HEALTH_URL="${HEALTH_URL:-https://spotacus.app/up}"
SMOKE_URL="${SMOKE_URL:-https://spotacus.app/login}"
DC=(docker compose -p spotacus -f docker/compose.prod.yml --env-file docker/.env.prod)
MAINTENANCE_ENABLED=false

on_failure() {
    local status=$?
    trap - ERR
    if [[ "$MAINTENANCE_ENABLED" == true ]]; then
        "${DC[@]}" exec -T app php artisan down --no-interaction || true
    fi
    printf '%s %s %s failed\n' "$RELEASE_TAG" "$RELEASE_COMMIT" "$APP_IMAGE_REF" >> "$STATE_DIR/history.log"
    "${DC[@]}" logs --no-color --tail=100 app nginx queue scheduler || true
    echo "Deployment failed; maintenance is retained if enabled. The last successful release is in $STATE_DIR/current.env" >&2
    exit "$status"
}
trap on_failure ERR

"${DC[@]}" config --quiet
"${DC[@]}" pull app queue scheduler
"${DC[@]}" up -d --wait --wait-timeout 180 mysql redis

if [[ -n "$("${DC[@]}" ps --status running -q app)" ]]; then
    "${DC[@]}" exec -T app php artisan down --no-interaction
    MAINTENANCE_ENABLED=true
fi
"${DC[@]}" stop --timeout 120 queue scheduler

if [[ "${SKIP_MIGRATIONS:-false}" != true ]]; then
    "${DC[@]}" run --rm --no-deps app php artisan migrate --force --no-interaction
fi

"${DC[@]}" stop app nginx
"${DC[@]}" rm -f app nginx
if docker volume inspect spotacus_public-build > /dev/null 2>&1; then
    docker volume rm spotacus_public-build
fi

"${DC[@]}" up -d --no-deps --wait --wait-timeout 180 app
MAINTENANCE_ENABLED=true
"${DC[@]}" exec -T app php artisan optimize:clear --no-interaction
"${DC[@]}" exec -T app php artisan config:cache --no-interaction
"${DC[@]}" exec -T app php artisan route:cache --no-interaction
"${DC[@]}" exec -T app sh -c 'test -L public/storage || php artisan storage:link --no-interaction'
"${DC[@]}" up -d --no-deps --wait --wait-timeout 180 nginx
"${DC[@]}" exec -T app php artisan up --no-interaction

curl --fail --silent --show-error --retry 5 --retry-delay 2 --max-time 15 "$HEALTH_URL"
curl --fail --silent --show-error --max-time 15 "$SMOKE_URL" > /dev/null
"${DC[@]}" up -d --no-deps --force-recreate --wait --wait-timeout 180 queue scheduler

awk '!/^APP_IMAGE_REF=/' docker/.env.prod > "$STATE_DIR/runtime.env.tmp"
printf 'APP_IMAGE_REF=%s\n' "$APP_IMAGE_REF" >> "$STATE_DIR/runtime.env.tmp"
chmod --reference=docker/.env.prod "$STATE_DIR/runtime.env.tmp"
mv "$STATE_DIR/runtime.env.tmp" docker/.env.prod

if [[ -f "$STATE_DIR/current.env" ]]; then
    cp "$STATE_DIR/current.env" "$STATE_DIR/previous.env"
fi
printf 'APP_IMAGE_REF=%s\nRELEASE_COMMIT=%s\nRELEASE_TAG=%s\n' \
    "$APP_IMAGE_REF" "$RELEASE_COMMIT" "$RELEASE_TAG" > "$STATE_DIR/current.env.tmp"
mv "$STATE_DIR/current.env.tmp" "$STATE_DIR/current.env"
printf '%s %s %s successful\n' "$RELEASE_TAG" "$RELEASE_COMMIT" "$APP_IMAGE_REF" >> "$STATE_DIR/history.log"
echo "Released $RELEASE_TAG ($RELEASE_COMMIT) using $APP_IMAGE_REF"
