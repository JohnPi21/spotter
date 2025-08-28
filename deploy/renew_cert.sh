#!/usr/bin/env bash
set -Eeuo pipefail

REPO="/var/www/spotacus"
DC="docker compose -f $REPO/docker/compose.prod.yml --env-file $REPO/docker/.env.prod -p spotacus"
DC_ACME="docker compose -f $REPO/docker/compose.prod.yml -f $REPO/docker/compose.acme.yml --env-file $REPO/docker/.env.prod -p spotacus"

DOMAIN="spotacus.app"
EMAIL="imalex96ro@gmail.com"
LIVE_DIR="$REPO/docker/certbot/letsencrypt/live/$DOMAIN"

echo "🔎 Checking existing certs for $DOMAIN…"
if [ ! -f "$LIVE_DIR/fullchain.pem" ] || [ ! -f "$LIVE_DIR/privkey.pem" ]; then
  echo "🌐 Starting Nginx (HTTP-only) for ACME challenge…"
  $DC_ACME up -d nginx

  echo "🔐 Requesting certificate via webroot…"
  $DC --profile certbot run --rm certbot \
    certonly --webroot -w /var/www/certbot \
    -d "$DOMAIN" -d "www.$DOMAIN" \
    --email "$EMAIL" --agree-tos --no-eff-email

  echo "🔁 Switching to full TLS config & reloading…"
  $DC up -d nginx
  $DC exec -T nginx nginx -t
  $DC exec -T nginx nginx -s reload
else
  echo "✅ Certs already present at $LIVE_DIR"
fi