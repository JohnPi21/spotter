#!/bin/bash

echo "📦 Running Laravel post-deploy setup..."

docker compose exec app php artisan migrate --force --retry=5
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan storage:link

echo "✅ Laravel post-deploy complete!"