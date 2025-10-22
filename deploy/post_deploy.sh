#!/usr/bin/env bash
set -Eeuo pipefail

# ===== Config you may tweak =====
PROJECT="spotacus"
HEALTH_URL="${HEALTH_URL:-https://spotacus.app/health}"
# =================================

# Find repo root (works both inside/outside a git repo)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR" && git rev-parse --show-toplevel 2>/dev/null || echo "$SCRIPT_DIR")"

COMPOSE_FILE="$REPO_ROOT/docker/compose.prod.yml"

# Build the docker compose command as an ARRAY (no eval, safe quoting)
DC=(docker compose -f "$COMPOSE_FILE" -p "$PROJECT")

trap 'echo -e "\n❌ Failed at line $LINENO: $BASH_COMMAND"; exit 1' ERR
echo "📦 Laravel post-deploy setup"

# ---- Small helpers ----
artisan()  { "${DC[@]}" exec -T app php artisan "$@"; }
app_sh()   { "${DC[@]}" exec -T app bash -lc "$*"; }

# 0) Ensure the core services are up
"${DC[@]}" up -d mysql redis app

# 2) Clear caches
echo "🧹 Clearing caches"
artisan optimize:clear

# 3) Maintenance mode (secret lets you bypass the 503 for health checks)
MAINT_SECRET="${DEPLOY_MAINT_SECRET:-$(openssl rand -hex 8)}"
echo "🛠️ Maintenance on (secret=$MAINT_SECRET)"
artisan down --secret="$MAINT_SECRET" || true

# 4) Migrate
echo "🗃️ Running migrations"
artisan migrate --force --no-interaction

# 5) Rebuild caches
echo "🧰 Rebuilding caches"
artisan config:cache
artisan route:cache
# artisan view:cache
# artisan event:cache

# 6) Ensure storage link
if "${DC[@]}" exec -T app test -L public/storage; then
  echo "🔗 Storage link OK"
else
  artisan storage:link || echo "ℹ️ storage:link skipped"
fi

# 7) Up again
echo "✅ Leaving maintenance"
artisan up || true

# 8) Smoke check
echo "🔎 Smoke check: $HEALTH_URL"
if ! curl -fsS --max-time 10 "$HEALTH_URL"; then
  echo "❌ Smoke check failed; recent logs:"
  "${DC[@]}" logs --tail=200 app nginx || true
  exit 1
fi

echo "🎉 Post-deploy complete!"
