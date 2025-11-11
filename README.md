<p align="center">
  <!-- LOGO PLACEHOLDER -->
  <img src="docs/assets/logo.png" alt="Vetlio Logo" height="64" />
</p>

# Vetlio

A modern, multi-tenant CRM for veterinary practices — scheduling clients & patients, invoicing, medical documents, offers, orders, and more. Built with **Laravel 12** and **Filament 4.1**.

> WARNING: **Alpha Stage** — not production ready. Expect breaking changes and incomplete features.

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/login.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/waiting-room.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/users.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/edit-service.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/invoice-form.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/invoice-view.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/portal.png" alt="Screenshot placeholder 1" width="720" />
</p>

<p align="center">
  <!-- SCREENSHOT PLACEHOLDERS -->
  <img src="docs/screenshots/portal-appointment-request.png" alt="Screenshot placeholder 1" width="720" />
</p>

---

## Features

* Appointments: clients & patients, slot availability, reminders
* Billing: invoices, items, taxes, payments
* Medical docs: findings, attachments, print/PDF
* Multi‑tenancy (SaaS)
* Role & permission model (per user / per location)
* Search, filters, exports, PDF prints

## Tech Stack

* **Laravel 12**, PHP 8.3+
* **Filament 4.1** (panels, resources, actions)
* MySQL/MariaDB, Redis (cache/queue)
* Pest for tests
* Docker/Sail (optional)

---

## Quick Start

### Prerequisites

* PHP 8.3+, Composer
* Node 20+, PNPM/NPM
* MySQL 8+ (or MariaDB 10.6+), Redis
* (Optional) Docker & Docker Compose

### 1) Clone & install

```bash
git clone https://github.com/lukacavic/vetlio.git
cd vetlio

composer install
cp .env.example .env
php artisan key:generate
```

### 2) Configure `.env`

```env
APP_NAME=Vetlio
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=https://vetlio.test

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vetlio
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@vetlio.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"


```

### 3) Database & storage

```bash
php artisan migrate --seed   # seeds demo data if available
php artisan storage:link
```

### 4) Assets & dev server

```bash
npm install
npm run dev
```

### 5) Run app

```bash
php artisan serve
# queues & scheduler
php artisan queue:work
# Add cron: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

* Add feature tests for tenancy scoping and permissions.

---

## Contributing

1. Fork & create a feature branch
2. Run tests and static analysis
3. Open a PR with a clear description & screenshots (if UI)

Code style: `vendor/bin/pint` · Static analysis: `vendor/bin/phpstan analyse`
