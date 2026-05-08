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

```bash
php artisan db:seed
```

Seeders include:
- `Database\\Seeders\\RolesAndUsersSeeder`: roles + example users
- `Database\\Seeders\\DemoChecklistSeeder`: demo template/questions + a sample instance/answers

## Test credentials

Seeded users (from `database/seeders/RolesAndUsersSeeder.php`):

- **Admin**: `admin@example.com` / `password`
- **Auditor**: `auditor@example.com` / `password`

## API authentication (Sanctum)

### Login

`POST /api/v1/auth/login`

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

### Logout

`POST /api/v1/auth/logout` (requires `auth:sanctum`)

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

All endpoints are prefixed with `/api/v1`.

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

