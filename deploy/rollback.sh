#!/usr/bin/env bash
set -Eeuo pipefail

if [[ "${SCHEMA_COMPATIBLE:-false}" != true ]]; then
    echo "Confirm the previous code supports the current database schema by setting SCHEMA_COMPATIBLE=true." >&2
    exit 1
fi

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$REPO_ROOT"
exec 9>.git/deploy.lock
flock -n 9
TARGET=previous
if [[ -f .git/releases/history.log && "$(tail -n 1 .git/releases/history.log)" == *' failed' ]]; then
    TARGET=current
fi
test -f ".git/releases/$TARGET.env"
unset APP_IMAGE_REF RELEASE_COMMIT RELEASE_TAG

while IFS='=' read -r key value; do
    case "$key" in
        APP_IMAGE_REF|RELEASE_COMMIT|RELEASE_TAG) export "$key=$value" ;;
        *) echo "Unexpected release metadata" >&2; exit 1 ;;
    esac
done < ".git/releases/$TARGET.env"

[[ "$RELEASE_COMMIT" =~ ^[a-f0-9]{40}$ ]]
git diff --quiet
git diff --cached --quiet
git checkout --detach "$RELEASE_COMMIT"
export SKIP_MIGRATIONS=true
bash deploy/post_deploy.sh
