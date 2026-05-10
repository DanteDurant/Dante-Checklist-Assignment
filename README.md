# Compliance Checklist Assessment (Laravel 11)

Compliance checklist system: admins maintain templates and questions; auditors complete assessments.

- **Auth**: Sanctum (API tokens) + session cookies for the web UI  
- **Roles**: Spatie Permission (`admin` / `auditor`) + policies

## Docker Compose (recommended)

Stack: **PHP 8.3-FPM**, **Nginx**, **MySQL 8**, **queue worker** (PDF jobs), optional **Vite** profile.

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose v2)

### Architecture

| Service | Image / build | Role |
|--------|----------------|------|
| **nginx** | `nginx:1.27-alpine` | HTTP on host port **8080** → Laravel `public/` |
| **app** | `docker/php/Dockerfile` | PHP-FPM + Composer + Node 20 (npm in same image) |
| **mysql** | `mysql:8.0` | Database + persistent volume `mysql-data` |
| **queue** | same as **app** | `php artisan queue:work database` (PDF export jobs) |
| **vite** (optional profile) | `node:20-bookworm-slim` | `npm run dev` with HMR on port **5173** |

Config files live under **`docker/nginx/`** and **`docker/php/`**.

### First-time setup (exact commands)

From the **`checklist-assignment/`** directory (repository root that contains `artisan`):

```bash
# 0) Environment (DB host, APP_URL, Sanctum domains — must match Compose)
cp docker/env.docker.example .env

# 1) Containers (`app` / `queue` read `.env` via env_file)
docker compose up -d --build

# 2) App key & dependencies
docker compose exec app php artisan key:generate
docker compose exec app composer install

# 3) Database
docker compose exec app php artisan migrate --seed

# 4) Frontend (production asset build — served by Vite-manifest from public/build)
docker compose exec app npm install
docker compose exec app npm run build
```

If you prefer to start Compose before editing secrets, copy **`.env.example`** to **`.env`** first (`touch .env` is enough so Compose starts), then set **`DB_HOST=mysql`**, **`DB_DATABASE=laravel`**, **`DB_USERNAME=laravel`**, **`DB_PASSWORD=laravel_secret`**, **`APP_URL=http://localhost:8080`**, and **`SANCTUM_STATEFUL_DOMAINS`** as in **`docker/env.docker.example`** before running **`migrate`**.

Open **http://localhost:8080**. Seeded logins: **`admin@example.com` / `password`**, **`auditor@example.com` / `password`**.

**API base URL** with this stack: **`http://localhost:8080/api/...`**.

### Environment variables (Docker)

- Copy **`docker/env.docker.example`** → **`.env`** (or merge DB / `APP_URL` / `SANCTUM_STATEFUL_DOMAINS` into `.env`).
- MySQL in Compose uses database **`laravel`**, user **`laravel`**, password **`laravel_secret`** (matches the example file). Change both **`.env`** and **`docker-compose.yml`** if you customize credentials.
- Ports: **`DOCKER_WEB_PORT`** (default `8080`), **`DOCKER_MYSQL_PORT`** (default **`3307`** — avoids clashes with a host MySQL on 3306), **`DOCKER_VITE_PORT`** (default `5173`). Inside Compose, Laravel still uses **`DB_HOST=mysql`** and **`DB_PORT=3306`** (container port).

### Queue worker (PDF exports)

The **`queue`** service runs continuously. Async PDF jobs use **`QUEUE_CONNECTION=database`**; the `jobs` table is created by Laravel migrations.

Verify the worker:

```bash
docker compose logs -f queue
```

### Optional: Vite dev server (hot reload)

Production evaluators can rely on **`npm run build`** only. For HMR:

```bash
docker compose --profile vite up -d
```

The Vite container sets **`DOCKER_VITE=1`** so `vite.config.js` listens on `0.0.0.0` and uses polling for bind mounts. Use **`APP_URL=http://localhost:8080`** and include **`localhost:5173`** in **`SANCTUM_STATEFUL_DOMAINS`** (see `docker/env.docker.example`).

### Useful commands

```bash
docker compose ps
docker compose exec app php artisan tinker
docker compose exec app php artisan queue:failed
docker compose down               # stop; add -v to drop MySQL volume
```

### Troubleshooting

| Symptom | What to try |
|--------|-------------|
| **Port 8080 / 3307 / 5173 in use** | `export DOCKER_WEB_PORT=18080 DOCKER_MYSQL_PORT=13306 DOCKER_VITE_PORT=25173` then `docker compose up -d`. To expose MySQL on host **3306** instead of **3307**: `DOCKER_MYSQL_PORT=3306` (requires nothing else listening on 3306). |
| **`Connection refused` to MySQL** | Wait for healthcheck (`docker compose ps`); ensure `.env` has `DB_HOST=mysql` |
| **403 / CSRF / login issues** | Set **`APP_URL`** to the URL you use (**`http://localhost:8080`**) and widen **`SANCTUM_STATEFUL_DOMAINS`** |
| **Permission errors on `storage/`** | `docker compose exec app chown -R www-data:www-data storage bootstrap/cache` |
| **Blank page / no CSS** | Run **`npm run build`** inside **`app`**, or enable **`vite`** profile |
| **Queued PDFs never finish** | Check **`queue`** logs; ensure migrations ran (`jobs` table). Run `docker compose restart queue` |
| **Missing `vendor` / `node_modules`** | `docker compose exec app composer install` and `npm install` |
| **Stale config** | `docker compose exec app php artisan config:clear` |
| **`Unknown column 'deleted_at'`** / seeder fails | Run **`docker compose exec app php artisan migrate`** so **`2026_05_10_*_add_soft_deletes_to_checklist_templates`** applies (templates use soft deletes). |
| **`php artisan test` fails with MySQL “mysql” host** | Tests use **SQLite in-memory** via **`phpunit.xml`** — ensure it includes **`DB_CONNECTION=sqlite`** / **`DB_DATABASE=:memory:`** (default in this repo). |

---

## Setup instructions (native PHP — without Docker)

### Requirements

- PHP **8.3+**
- Composer
- SQLite (default) or MySQL/Postgres

### Install dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Environment setup

This project defaults to **SQLite**.

### SQLite (recommended for local/dev)

```bash
touch database/database.sqlite
```

In `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

> If you omit `DB_DATABASE`, Laravel will default to `database/database.sqlite`.

## Migration steps

```bash
php artisan migrate
```

## Seed steps

Populate a full demo dataset (recommended after schema changes):

```bash
php artisan migrate:fresh --seed
```

`DatabaseSeeder` runs, in order: `RolesSeeder`, `UserSeeder`, `ChecklistTemplateSeeder`, `ChecklistQuestionSeeder`, `ChecklistInstanceSeeder`, `ChecklistAnswerSeeder` (realistic templates, auditors, reporting-friendly instances, and type-correct answers).

## Test credentials

Seeded users (`UserSeeder`):

- **Admin**: `admin@example.com` / `password`
- **Auditor**: `auditor@example.com` / `password`

## API authentication (Sanctum)

### Two API surfaces

This project exposes:

- **Stable external API** (recommended for evaluation): **`/api/*`**
- Internal versioned API (legacy/dev): `/api/v1/*`

The remainder of this section focuses on the **stable `/api/*`** endpoints.

### Login (get a Bearer token)

`POST /api/login`

Body:

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "local"
}
```

Response:

```json
{
  "success": true,
  "message": "Logged in",
  "data": {
    "token": "<plain-text-token>",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "roles": ["admin"],
      "permissions": []
    }
  }
}
```

Send the token on subsequent requests:

```http
Authorization: Bearer <plain-text-token>
```

### Me (verify token)

`GET /api/me` (requires `auth:sanctum`)

### Logout (revoke tokens)

`POST /api/logout` (requires `auth:sanctum`)

## Running the application

```bash
php artisan serve
```

## Running tests

This project uses **PHPUnit** (see `phpunit.xml`). **`phpunit.xml` pins `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`** so the suite does not depend on MySQL or your personal `.env` (for example Docker’s `DB_HOST=mysql`). Full layout and coverage notes: **`docs/testing.md`**.

```bash
# Full suite
php artisan test

# Examples: feature-only, name filter
php artisan test tests/Feature
php artisan test --filter=PublicApi

# Reset demo DB (optional; tests auto-migrate via RefreshDatabase)
php artisan migrate:fresh --seed
```

## Project layout

- **HTTP layer**
  - Controllers: stable **`app/Http/Controllers/Api/**`** (`/api/*`) and versioned **`app/Http/Controllers/Api/V1/**`** (`/api/v1/*`)
  - Validation: `app/Http/Requests/**` (Form Requests)
  - Serialization: `app/Http/Resources/**` (API Resources)

- **Application layer**
  - Use-case services and query objects: `app/Application/**`
  - Transactions and invariants live here (thin controllers)
  - Examples:
    - `app/Application/Auth/Services/TokenAuthService.php`
    - `app/Application/ChecklistTemplates/Services/ChecklistTemplateService.php`
    - `app/Application/Assessments/Services/ChecklistCompletionService.php`
    - `app/Application/Reporting/Queries/ChecklistInstanceReportQuery.php`

- **Domain model**
  - Eloquent models: `app/Models/**`
  - Enums: `app/Enums/**` (status/type casting)

- **Authorization**
  - Spatie middleware aliases registered in `bootstrap/app.php`
  - Policies registered via `Gate::policy(...)` in `app/Providers/AppServiceProvider.php`

## API documentation summary

- **`docs/api.md`** — endpoints, PDF export behavior, Postman, troubleshooting  
- **`docs/testing.md`** — how tests are organized  
- **`postman/Compliance-Management-System.postman_collection.json`** — import into Postman (includes PDF exports)

### PDF exports and queues

Small exports can return a PDF immediately; larger ones use the **`database`** queue (`jobs` table). Thresholds live in **`config/pdf_exports.php`**.

For **local / Docker demos**, the compliance snapshot defaults to **inline** generation up to **`PDF_SNAPSHOT_FORCE_SYNC_MAX_INSTANCES`** (25000 instances when `APP_ENV=local`) so the dashboard does not depend on a worker for typical seeded data. Override with:

```env
# Omit for production (use queue thresholds). In local dev, omit to keep the safe default sync cap.
PDF_SNAPSHOT_FORCE_SYNC_MAX_INSTANCES=25000
# Disable inline snapshot (always use queue rules): set to 0
PDF_SNAPSHOT_FORCE_SYNC_MAX_INSTANCES=0

# Quiet export lifecycle logs in production if desired:
# PDF_EXPORT_LOG_LIFECYCLE=false
```

**Production / large snapshots:** install a persistent worker:

```bash
php artisan queue:work
```

- Ensure `.env` has `QUEUE_CONNECTION=database` when using the database driver, and run `php artisan migrate` so the **`jobs`** and **`failed_jobs`** tables exist.

**Stuck export UI:** check **`storage/logs/laravel.log`** (`pdf_export.*`). In the browser console:

```js
localStorage.setItem('PDF_EXPORT_DEBUG', '1');
```

Reload, retry export, then inspect `[pdf-export]` messages.

### Stable external API (`/api/*`)

Auth:

- `POST /api/login`
- `POST /api/logout` (auth)
- `GET /api/me` (auth)

Admin (requires `auth:sanctum` + `role:admin`):

- Templates: `GET/POST /api/templates`, `GET/PUT/DELETE /api/templates/{template}` (`DELETE` **soft-deletes** the template; checklist history is kept)
- Questions: `POST /api/templates/{template}/questions`, `PUT/DELETE /api/questions/{question}`
- Reports: `GET /api/reports`

Auditor (requires `auth:sanctum` + `role:auditor`):

- `GET /api/checklists`
- `POST /api/checklists/start/{template}`
- `GET /api/checklists/{checklist}`
- `PUT /api/checklists/{checklist}/save-draft`
- `PUT /api/checklists/{checklist}/complete`

#### PDF / exports (admin or auditor, where noted)

- Unified JSON API: `POST /api/exports/pdf`, `GET /api/exports`, `GET /api/exports/{uuid}`
- Legacy direct PDF URLs: `GET /api/reports/export-pdf`, `GET /api/reports/compliance-snapshot/export-pdf`, `GET /api/reports/auditor-activity/export-pdf`, `GET /api/templates/{template}/export-pdf`, `GET /api/checklists/{checklist}/export-pdf`

---

### Internal API (`/api/v1/*`)

### Auth

- `POST /auth/login`
- `POST /auth/logout` (auth)
- `GET /me` (auth)

### Admin (requires `auth:sanctum` + `role:admin`)

- `GET /admin/ping`

Templates:
- `GET /checklist-templates`
- `POST /checklist-templates`
- `GET /checklist-templates/{template}`
- `PATCH /checklist-templates/{template}`
- `DELETE /checklist-templates/{template}`

Questions (nested + shallow):
- `GET /checklist-templates/{template}/questions`
- `POST /checklist-templates/{template}/questions`
- `GET /questions/{question}`
- `PATCH /questions/{question}`
- `DELETE /questions/{question}`

Reporting:
- `GET /admin/reports/checklist-instances`
  - Filters: `date_from`, `date_to`, `template_id`, `auditor_id`
  - Optional: `q` (search), `per_page`

### Auditor (requires `auth:sanctum` + `role:auditor`)

- `GET /auditor/ping`

Checklist completion flow:
- `GET /auditor/checklist-instances`
- `POST /auditor/checklist-instances` (start instance)
- `GET /auditor/checklist-instances/{instance}`
- `PUT /auditor/checklist-instances/{instance}/answers` (save draft progress)
- `POST /auditor/checklist-instances/{instance}/complete` (submit + lock)

## Submission checklist (evaluators)

1. **`.env`**: Docker → copy **`docker/env.docker.example`**; native → copy **`.env.example`** (see setup sections above).
2. **Dependencies & DB**: **`composer install`**, **`php artisan key:generate`**, **`php artisan migrate --seed`** (Docker: use **First-time setup** commands).
3. **Assets**: **`npm install`** && **`npm run build`** (in **`app`** container if using Docker).
4. **Smoke test**: **http://localhost:8080** — **`admin@example.com` / `password`** (see **Test credentials**).
5. **Tests**: **`php artisan test`** (PHPUnit uses SQLite **`:memory:`** from **`phpunit.xml`**, not your MySQL `.env`).
6. **API**: **`POST /api/login`** → Bearer token; details in **`docs/api.md`** and the Postman collection.

## Notes

- Templates and instances expose a **`public_id`** (ULID) for stable external references.
- **Deleting** a template **archives** it (soft delete); existing checklist instances stay linked for reporting (`docs/api.md`).
- **`tests/Feature`** covers auth, RBAC, templates, completion, reporting, PDF exports, and validation.

