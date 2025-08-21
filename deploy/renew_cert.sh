#!/bin/bash

echo "🟢 Starting temporary nginx-certbot for challenge..."
docker compose up -d nginx-certbot

echo "🔁 Running certbot..."
docker compose run --rm certbot certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  --email imalex96ro@gmail.com \
  --agree-tos \
  --no-eff-email \
  -d spotacus.app -d www.spotacus.app

echo "🛑 Stopping nginx-certbot..."
docker compose stop nginx-certbot

echo "📂 Fixing permissions for certbot folder..."
sudo chown -R $USER:$USER ./certbot

# Optional: Copy certs to static folder for easier Nginx mount
echo "📂 Copying certs to static path..."
mkdir -p ./certbot/static/spotacus.app
cp ./certbot/letsencrypt/live/spotacus.app/fullchain.pem ./certbot/static/spotacus.app/
cp ./certbot/letsencrypt/live/spotacus.app/privkey.pem ./certbot/static/spotacus.app/

echo "🚀 Starting nginx..."
docker compose up -d nginx

echo "✅ Done! Cert renewed and Nginx reloaded."
