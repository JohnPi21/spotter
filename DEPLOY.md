# 🚀 Spotacus Deployment Guide

This document is a **runbook** for deploying Spotacus in production on a fresh server (EC2, VPS, etc.).  
It assumes Docker & Docker Compose v2 are installed.

---

## ⚡ Quick Start (first deployment)

```bash
# 1. Clone repo & enter project
git clone git@github.com:your-org/spotacus.git
cd spotacus

# 2. Prepare production env
cp docker/.env.prod.example docker/.env.prod
nano docker/.env.prod   # update APP_KEY, DB creds, etc.

# 3. Issue SSL certs (first time only)
./deploy/renew_cert.sh

# 4. Bring up the stack
docker compose -f docker/compose.prod.yml --env-file docker/.env.prod -p spotacus up -d --remove-orphans

# 5. Run post-deploy tasks (migrations, caches, health check)
./deploy/post_deploy.sh
```

````

---

## 🔄 Redeploy (after pushing new code/image)

```bash
git fetch origin
git reset --hard origin/prod

docker compose -f docker/compose.prod.yml --env-file docker/.env.prod -p spotacus pull
docker compose -f docker/compose.prod.yml --env-file docker/.env.prod -p spotacus up -d --remove-orphans

./deploy/post_deploy.sh
```

---

## 🔐 SSL Certificate Renewal

Certificates are issued with Let’s Encrypt and live under `docker/certbot/letsencrypt/`.

Renew manually:

```bash
./deploy/renew_cert.sh
```

Or add a cron job (recommended):

```
0 3 * * * docker compose -f /var/www/spotacus/docker/compose.prod.yml --env-file /var/www/spotacus/docker/.env.prod -p spotacus run --rm --profile certbot certbot renew && docker compose -f /var/www/spotacus/docker/compose.prod.yml -p spotacus exec -T nginx nginx -s reload
```

---

## 🛠️ Useful Commands

```bash
# Check container status
docker compose -f docker/compose.prod.yml -p spotacus ps

# Tail app logs
docker compose -f docker/compose.prod.yml -p spotacus logs -f app

# Run artisan commands
docker compose -f docker/compose.prod.yml --env-file docker/.env.prod -p spotacus exec app php artisan migrate --force
```

---

✅ That’s all you need for day-to-day operations:

- **First deploy** → Quick Start
- **Subsequent deploys** → Redeploy section
- **Certs** → Renewal script/cron
- **Maintenance** → Useful commands

```
````
