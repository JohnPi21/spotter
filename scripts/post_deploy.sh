#!/bin/bash

echo "📦 Running Laravel post-deploy setup..."

echo "⏳ Waiting for MySQL to be ready..."
for i in {1..30}; do
    if docker compose exec -T app php -r '
        try {
            new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
            echo "✅ DB ready\n";
            exit(0);
        } catch (Exception $e) {
            exit(1);
        }'; then
        break
    fi
    echo "⏳ Waiting for DB ($i/30)..."
    sleep 2
done

docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan storage:link

echo "✅ Laravel post-deploy complete!"