#!/usr/bin/env bash
set -euo pipefail

: "${RELEASE_TAG:?Supply the Git release tag}"
[[ "$RELEASE_TAG" =~ ^v[A-Za-z0-9_.-]+$ ]]
COMMIT=$(git rev-parse HEAD)
git merge-base --is-ancestor "$COMMIT" origin/main
printf '%s\n' "$COMMIT"
