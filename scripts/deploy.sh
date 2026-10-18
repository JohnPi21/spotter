#!/usr/bin/env bash
set -euo pipefail
SSH_DIR=$(mktemp -d "${RUNNER_TEMP:-${TMPDIR:-/tmp}}/spotacus-ssh.XXXXXX")
trap 'rm -f "$SSH_DIR/deploy_key" "$SSH_DIR/known_hosts"; rmdir "$SSH_DIR"' EXIT
printf '%s\n' "$EC2_SSH_KEY" > "$SSH_DIR/deploy_key"
chmod 600 "$SSH_DIR/deploy_key"
printf '%s\n' "$EC2_HOST_KEY" > "$SSH_DIR/known_hosts"
chmod 600 "$SSH_DIR/known_hosts"
[[ "$GHCR_USERNAME" =~ ^[A-Za-z0-9_-]+$ ]]
[[ "$RELEASE_COMMIT" =~ ^[a-f0-9]{40}$ ]]
[[ "$RELEASE_TAG" =~ ^v[A-Za-z0-9_.-]+$ ]]
[[ "$APP_IMAGE_REF" =~ ^ghcr.io/[a-z0-9_./-]+@sha256:[a-f0-9]{64}$ ]]
SSH=(ssh -i "$SSH_DIR/deploy_key" -o UserKnownHostsFile="$SSH_DIR/known_hosts" -o BatchMode=yes -o StrictHostKeyChecking=yes "$EC2_USER@$EC2_HOST")
printf '%s\n' "$GHCR_TOKEN" | "${SSH[@]}" "docker login ghcr.io --username '$GHCR_USERNAME' --password-stdin"
"${SSH[@]}" "bash -s -- '$RELEASE_COMMIT' '$APP_IMAGE_REF' '$RELEASE_TAG'" <<'REMOTE'
set -euo pipefail
cd /var/www/spotacus
exec 9>.git/deploy.lock
flock -n 9
git diff --quiet
git diff --cached --quiet
git fetch origin "$1"
git checkout --detach "$1"
export RELEASE_COMMIT="$1" APP_IMAGE_REF="$2" RELEASE_TAG="$3"
bash deploy/post_deploy.sh
REMOTE
printf 'Released `%s`\n\nCommit: `%s`\n\nImage: `%s`\n' "$RELEASE_TAG" "$RELEASE_COMMIT" "$APP_IMAGE_REF" >> "$GITHUB_STEP_SUMMARY"
