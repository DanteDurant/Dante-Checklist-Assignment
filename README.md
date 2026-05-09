# Compliance Checklist Assessment (Laravel 11)

Laravel 11 REST API for managing compliance checklist templates (admin) and completing checklist assessments (auditor).

- **Authentication**: Laravel Sanctum (token-based)
- **Authorization**: Spatie Laravel Permission (roles + middleware) + Laravel Policies

## Setup instructions

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

This project uses **PHPUnit** (already configured).

```bash
php artisan test
```

## Project architecture overview

High-level layering (clean architecture style):

- **HTTP layer**
  - Controllers: `app/Http/Controllers/Api/V1/**`
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

Evaluator docs:

- API guide: `docs/api.md`
- Postman collection: `postman/Compliance-Management-System.postman_collection.json`

### Stable external API (`/api/*`)

Auth:

- `POST /api/login`
- `POST /api/logout` (auth)
- `GET /api/me` (auth)

Admin (requires `auth:sanctum` + `role:admin`):

- Templates: `GET/POST /api/templates`, `GET/PUT/DELETE /api/templates/{template}`
- Questions: `POST /api/templates/{template}/questions`, `PUT/DELETE /api/questions/{question}`
- Reports: `GET /api/reports`

Auditor (requires `auth:sanctum` + `role:auditor`):

- `GET /api/checklists`
- `POST /api/checklists/start/{template}`
- `GET /api/checklists/{checklist}`
- `PUT /api/checklists/{checklist}/save-draft`
- `PUT /api/checklists/{checklist}/complete`

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

## Notes

- Templates and instances include a `public_id` ULID intended for safe external exposure.
- Feature tests live in `tests/Feature` and cover auth, roles, template CRUD, completion rules, and reporting filters.

