# Production Setup (Laravel + Vue + Docker)

This document describes how to build and deploy the **production version** of the spotacus app. Local development uses [Laravel Sail](https://laravel.com/docs/sail), while **production** runs on a custom Docker-based setup.

---

## Overview

The production stack uses:

- **Laravel (PHP 8.3-FPM)**
- **Vue 3 (Vite build, SSR support)**
- **MySQL 8**
- **Redis 7**
- **Nginx** (with SSL via Let's Encrypt / Certbot)

---

## Docker Compose Setup

Production containers are defined in `docker-compose.yml`:

- `app` – Laravel application running PHP-FPM
- `nginx` – Serves the Laravel app via HTTPS (443)
- `mysql` – Database
- `redis` – Cache / Queue driver
- `certbot` – Used for issuing/renewing SSL certificates
- `nginx-certbot` – Temporary HTTP server for Certbot HTTP challenge

### Networks & Volumes

- **Networks:** `appnet` (isolated internal network)
- **Volumes:**
    - `mysql-prod-data` → MySQL persistent storage
    - `public-build` → Built frontend assets
    - `certbot-htdocs` → Certbot challenge directory

---

## Dockerfile (Production Build)

The Dockerfile uses **multi-stage builds**:

1. **Composer Dependencies (no-dev):**
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
2. **Frontend Build:**
    ```bash
    npm run build
    ```
    > `npm run build` uses devDependencies (e.g., `vue-tsc` for type checking).
3. **Final PHP Image:**
    - Copies app code, vendor files, and built assets
    - Installs PHP extensions (pdo_mysql, redis, bcmath, etc.)
    - Runs as `php-fpm`

---

## Environment Variables

Ensure `.env.prod` contains the correct values:

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

## Build & Run (Production)

```bash
# Build the application image
docker compose -f docker-compose.yml build

# Run the stack
docker compose -f docker-compose.yml up -d
```

---

## SSL Certificates (Certbot)

### Initial Setup

When deploying to a **new EC2 instance**, obtain SSL certificates before enabling HTTPS:

1. **Temporary HTTP Setup:**
    - Start `nginx-certbot` (HTTP-only) using `nginx.certbot.conf` on port 80.
    - Ensure Cloudflare or any reverse proxy does not redirect to HTTPS.
    - Confirm server responds with HTTP 200 on port 80.
2. **Issue Certificates:**
    ```bash
    docker compose --profile certbot up -d nginx-certbot
    docker run -it --rm \
      -v /etc/letsencrypt:/etc/letsencrypt \
      -v /var/lib/letsencrypt:/var/lib/letsencrypt \
      -v $(pwd)/certbot-htdocs:/data/letsencrypt \
      certbot/certbot certonly \
      --webroot \
      --webroot-path=/data/letsencrypt \
      --email imalex96ro@gmail.com \
      --agree-tos \
      --no-eff-email \
      --rsa-key-size 4096 \
      -d spotacus.app -d www.spotacus.app
    ```
3. **Persist Certificates:**
    ```bash
    cp -r ./certbot/letsencrypt /etc/letsencrypt-backup
    ```
4. **Configure Nginx:**
    ```nginx
    ssl_certificate     /etc/letsencrypt-backup/live/spotacus.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt-backup/live/spotacus.app/privkey.pem;
    ```
5. **Renew Certificates:**
    ```bash
    docker compose run --rm certbot renew
    ```

---

## Cloudflare Handling & SSH Keys

### Cloudflare HTTP-01 Challenge

- Disable Cloudflare "Always Use HTTPS" and redirect rules temporarily.
- Optionally enable Cloudflare Development Mode.
- Confirm EC2 responds on port 80.

### SSH Keys for GitHub

1. Generate key pair:
    ```bash
    ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
    ```
2. Add GitHub to known hosts:
    ```bash
    ssh-keyscan -t rsa github.com >> ~/.ssh/known_hosts
    ```
3. Add public key to GitHub Deploy Keys.
4. Set repo to use SSH:
    ```bash
    git remote set-url origin git@github.com:username/repo.git
    ```
5. Test connection:
    ```bash
    ssh -T git@github.com
    ```

---

## Utility Scripts

- **Post Deploy Script:** `/scripts/post_deploy.sh` – waits for DB, runs migrations, clears caches, and prepares Laravel for production.
    - Run manually:
        ```bash
        bash ./scripts/post_deploy.sh
        ```
- **Certificate Renewal Script:** `/scripts/renew_cert.sh` – spins up temporary HTTP challenge server, renews certificates, adjusts permissions, and restarts Nginx.
    - Run manually:
        ```bash
        bash ./scripts/renew_cert.sh
        ```

---

## CI/CD Pipeline (GitHub Actions)

- **Workflow:** `.github/workflows/deploy.yml`
- **Trigger:** Pushes to `prod` branch.

### Jobs:

1. **Test Stage:**
    - Uses `docker-compose.ci.yml` and `.env.ci`.
    - Installs dev dependencies, runs migrations and PHPUnit tests.
2. **Deploy Stage:**
    - SSH to EC2 using GitHub Secrets keys.
    - Pull latest `prod` branch, rebuild containers, and run `/scripts/post_deploy.sh`.

### Security:

- Secrets: `EC2_HOST`, `EC2_USER`, `EC2_SSH_KEY`.
- Deploy keys: EC2 → GitHub and vice versa.

---

## Next Steps

- [ ] Automate SSL renewal (cron or scheduled container).
- [ ] Implement rolling Laravel boot (zero downtime).

---

## References

- [Laravel Deployment Docs](https://laravel.com/docs/deployment)
- [Vue 3 + Vite SSR](https://vitejs.dev/guide/ssr.html)
- [Certbot](https://certbot.eff.org/)
- [Docker Compose](https://docs.docker.com/compose/)
- [GitHub Actions](https://docs.github.com/en/actions)
