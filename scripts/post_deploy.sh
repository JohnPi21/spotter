#!/usr/bin/env bash
set -Eeuo pipefail

echo "📦 Running Laravel post-deploy setup..."
trap 'echo "❌ Post-deploy failed on line $LINENO"; exit 1' ERR

# ---- Helpers ----
artisan() { docker compose exec -T app php artisan "$@"; }
app_exec() { docker compose exec -T app bash -lc "$*"; }

# Optional: maintenance secret (set via env or auto-gen)
MAINT_SECRET="${DEPLOY_MAINT_SECRET:-$(openssl rand -hex 8)}"

echo "⏳ Waiting for MySQL to be ready (inside app container)..."
DB_HOST=$(docker compose exec -T app printenv DB_HOST)
DB_PORT=$(docker compose exec -T app printenv DB_PORT)
DB_NAME=$(docker compose exec -T app printenv DB_DATABASE)
DB_USER=$(docker compose exec -T app printenv DB_USERNAME)
DB_PASS=$(docker compose exec -T app printenv DB_PASSWORD)

for i in {1..60}; do
  if docker compose exec -T app php -r "
    try {
      new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_NAME}','${DB_USER}','${DB_PASS}',[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);
      echo \"✅ DB ready\n\";
      exit(0);
    } catch (Throwable \$e) {
      fwrite(STDERR, \"⏳ Waiting for DB: \".\$e->getMessage().\"\\n\");
      exit(1);
    }"; then
    break
  fi
  sleep 2
done

echo "🧹 Clearing caches"
artisan optimize:clear

echo "🛠️  Entering maintenance for migration"
artisan down --secret="${MAINT_SECRET}" || true

echo "🗃️ Running migrations"
artisan migrate --force --no-interaction

echo "🧰 Rebuilding caches"
artisan config:cache
artisan route:cache
# artisan view:cache      # enable if you precompile views
# artisan event:cache     # optional

# Idempotent storage link
if docker compose exec -T app test -L public/storage; then
  echo "🔗 Storage link already present"
else
  artisan storage:link || echo "ℹ️ storage:link skipped (might already exist)"
fi

# If you run Horizon/queues/Octane, uncomment the relevant lines:
# artisan horizon:terminate || true
# artisan queue:restart || true
# artisan octane:reload || true

echo "✅ Bringing app out of maintenance"
artisan up || true

echo "🔎 Smoke check"
# Replace with your health route; add -k if self-signed TLS
curl -fsS --max-time 10 https://spotacus.app/health || {
  echo "❌ Smoke check failed; recent logs:"; 
  docker compose logs --tail=200 app nginx || true
  exit 1
}

echo "🎉 Laravel post-deploy complete!"
