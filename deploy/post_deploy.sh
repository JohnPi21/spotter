#!/usr/bin/env bash
set -Eeuo pipefail

# Resolve paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR" && git rev-parse --show-toplevel 2>/dev/null || echo "$SCRIPT_DIR")"

# Compose runner (edit paths/project to taste)
DC="docker compose -f \"$REPO_ROOT/docker/compose.prod.yml\" --env-file \"$REPO_ROOT/docker/.env.prod\" -p spotacus"

echo "📦 Running Laravel post-deploy setup..."
trap 'echo "❌ Post-deploy failed on line $LINENO"; exit 1' ERR

# ---- Helpers ----
artisan()  { eval "$DC exec -T app php artisan \"$*\""; }
app_exec() { eval "$DC exec -T app bash -lc \"$*\""; }

# Optional: ensure services are up
eval "$DC up -d"

# Optional: maintenance secret (set via env or auto-gen)
MAINT_SECRET="${DEPLOY_MAINT_SECRET:-$(openssl rand -hex 8)}"

echo "⏳ Waiting for MySQL to be ready (inside app container)..."
DB_HOST=$(eval "$DC exec -T app printenv DB_HOST")
DB_PORT=$(eval "$DC exec -T app printenv DB_PORT")
DB_NAME=$(eval "$DC exec -T app printenv DB_DATABASE")
DB_USER=$(eval "$DC exec -T app printenv DB_USERNAME")
DB_PASS=$(eval "$DC exec -T app printenv DB_PASSWORD")

# for i in {1..60}; do
#   if eval "$DC exec -T app php -r 'try{ new PDO(\"mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_NAME}\",\"${DB_USER}\",\"${DB_PASS}\",[ PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION ]); echo \"✅ DB ready\n\"; exit(0);}catch(Throwable \$e){fwrite(STDERR, \"⏳ Waiting for DB: \".\$e->getMessage().\"\\n\"); exit(1);}';"
#   then break; fi
#   sleep 2
# done

echo "🧹 Clearing caches"
artisan optimize:clear

echo "🛠️ Entering maintenance for migration"
artisan down --secret="${MAINT_SECRET}" || true

echo "🗃️ Running migrations"
artisan migrate --force --no-interaction

echo "🧰 Rebuilding caches"
artisan config:cache
artisan route:cache
# artisan view:cache
# artisan event:cache

# Idempotent storage link
if eval "$DC exec -T app test -L public/storage"; then
  echo "🔗 Storage link already present"
else
  artisan storage:link || echo "ℹ️ storage:link skipped (might already exist)"
fi

echo "✅ Bringing app out of maintenance"
artisan up || true

echo "🔎 Smoke check"
curl -fsS --max-time 10 https://spotacus.app/health || {
  echo "❌ Smoke check failed; recent logs:"
  eval "$DC logs --tail=200 app nginx" || true
  exit 1
}

echo "🎉 Laravel post-deploy complete!"
