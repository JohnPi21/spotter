# 🚀 Spotacus — Production Deployment (Laravel + Vue + Docker)

This document describes how to **build, deploy, and maintain** the production version of the **Spotacus** app.  
Local development uses [Laravel Sail](https://laravel.com/docs/sail), while **production** runs on a custom Docker stack.

---

## ⚡ Quick Start (new EC2 server)

```bash
# 1. Clone repo & enter project
git clone git@github.com:your-org/spotacus.git
cd spotacus

# 2. Set up production env
cp docker/.env.prod.example docker/.env.prod
nano docker/.env.prod   # update APP_KEY, DB creds, etc.

# 3. Issue SSL certs (first time only)
./deploy/renew_cert.sh

# 4. Deploy stack
docker compose -f docker/compose.prod.yml --env-file docker/.env.prod -p spotacus up -d --remove-orphans

# 5. Run post-deploy tasks
./deploy/post_deploy.sh
```

---

## 📂 Repository Structure (deployment-related)

```
docker/
 ├─ ci/                  # CI Dockerfile + docker-compose.ci.yml + .env.ci
 ├─ nginx/               # Nginx configs (prod & acme variants)
 │   ├─ nginx.prod.conf
 │   └─ nginx.acme.conf
 ├─ certbot/             # SSL certs (Let’s Encrypt) + ACME challenge volume
 ├─ .env.prod            # Production environment vars
 ├─ compose.prod.yml     # Main production stack
 ├─ compose.acme.yml     # Temporary override for first-time cert issuance
deploy/
 ├─ post_deploy.sh       # Runs after deploy: waits for DB, runs migrations, clears caches
 └─ renew_cert.sh        # Issues/renews SSL certs & reloads nginx
.github/workflows/
 └─ deploy.yml           # CI/CD workflow (build, test, promote, deploy)
```

---

## 🏗️ Stack Overview

- **Laravel (PHP 8.3-FPM)**
- **Vue 3 + Vite** (built into `/public/build`)
- **MySQL 8** (persistent named volume: `mysql-prod-data`)
- **Redis 7** (cache & queues)
- **Nginx** (reverse proxy + SSL termination)
- **Certbot** (for Let’s Encrypt issuance/renewal)

---

## ⚙️ Production Docker Compose (`compose.prod.yml`)

Services:

- `app` → Laravel app (PHP-FPM)
- `nginx` → serves app on ports 80/443
- `mysql` → database (persistent volume `mysql-prod-data`)
- `redis` → queues/cache
- `certbot` → used to issue/renew SSL certs (profile `certbot`)
- `queue` + `scheduler` → optional Laravel workers

Networks:

- `appnet` (internal)

Volumes:

- `mysql-prod-data` → MySQL persistent storage
- `public-build` → built frontend assets
- `certbot-htdocs` → ACME challenge

---

## 🔑 Environment Variables

`docker/.env.prod` (example):

```dotenv
APP_ENV=production
APP_KEY=base64:xxxx
APP_DEBUG=false

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=spotacus
DB_USERNAME=spotacus
DB_PASSWORD=secret
```

---

## 📜 Deployment Flow

### 1. Build & push app image (CI)

GitHub Actions builds & pushes a tagged image to GHCR on every push to `prod`.

### 2. Test stage (CI)

- Uses `docker/ci/` configs (`docker-compose.ci.yml`, `.env.ci`).
- Installs dev deps, runs migrations & PHPUnit tests.

### 3. Promote stage (CI)

- Retags tested digest as `prod-latest`.

### 4. Deploy stage (CI/CD)

- SSH into EC2 using GitHub secrets
- Pulls latest `prod` branch
- Runs:

    ```bash
    docker compose --env-file docker/.env.prod -f docker/compose.prod.yml -p spotacus pull
    docker compose --env-file docker/.env.prod -f docker/compose.prod.yml -p spotacus up -d --remove-orphans
    ./deploy/post_deploy.sh
    ```

---

## 🔐 SSL Certificates (Let’s Encrypt)

### First-time issuance

Certificates must exist **before** Nginx can serve HTTPS. Use the ACME override:

```bash
# Start nginx with HTTP-only config
docker compose -f docker/compose.prod.yml -f docker/compose.acme.yml -p spotacus up -d nginx

# Issue certs
./deploy/renew_cert.sh
```

This script:

- Starts Nginx (HTTP only)
- Runs Certbot with webroot method
- Reloads Nginx with full TLS config

### Renewal

Certificates auto-renew via cron (recommended):

```
0 3 * * * docker compose -f /var/www/spotacus/docker/compose.prod.yml --env-file /var/www/spotacus/docker/.env.prod -p spotacus run --rm --profile certbot certbot renew && docker compose -f /var/www/spotacus/docker/compose.prod.yml -p spotacus exec -T nginx nginx -s reload
```

Or manually run:

```bash
./deploy/renew_cert.sh
```

---

## 🔧 Utility Scripts

- **`deploy/post_deploy.sh`**
    - Waits for MySQL
    - Runs `php artisan migrate --force`
    - Clears/rebuilds caches
    - Ensures `storage:link`
    - Brings app out of maintenance
    - Health check via `/health`

- **`deploy/renew_cert.sh`**
    - Boots Nginx (HTTP-only)
    - Issues/renews certificates via Certbot
    - Reloads Nginx

---

## 🛡️ Nginx Configuration

- **`nginx.prod.conf`** → Full HTTPS + app serving
- **`nginx.acme.conf`** → Minimal HTTP-only config for ACME challenges
- Includes:
    - Rate limiting (`limit_req`)
    - Security headers (HSTS, X-Frame-Options, etc.)
    - Static asset caching (`/build/`, `/storage/`)
    - PHP-FPM proxy to `app:9000`

---

## ✅ Operational Notes

- Always run migrations with `php artisan migrate --force` in prod (never `migrate:fresh`).
- Database data is persisted via `mysql-prod-data` named volume.
- `.gitignore` should exclude:

    ```
    /docker/certbot/letsencrypt/
    /docker/storage/
    /docker/nginx.conf/
    ```

- Health checks:
    - `/health` (HTTP 200 plain) on port 80 → container check
    - `/health` route in Laravel (or `/nginx-health`) for HTTPS CI/CD smoke check

---

## 📦 CI/CD Pipeline

Located in `.github/workflows/deploy.yml`.
Jobs:

- **build** → build & push image
- **test** → run tests with CI stack
- **promote** → retag tested image as `prod-latest`
- **deploy** → SSH into EC2 and run deployment

Secrets required:

- `EC2_HOST`, `EC2_USER`, `EC2_SSH_KEY`
- `GHCR_USERNAME`, `GHCR_TOKEN`

---

## 📋 Next Steps

- [ ] Automate SSL renewal with cron/systemd
- [ ] Optionally add rolling deploys for zero downtime
- [ ] Harden MySQL (backups, monitoring)

---

## 📚 References

- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Vue + Vite Build](https://vitejs.dev/guide/)
- [Certbot](https://certbot.eff.org/)
- [Docker Compose](https://docs.docker.com/compose/)
- [GitHub Actions](https://docs.github.com/en/actions)