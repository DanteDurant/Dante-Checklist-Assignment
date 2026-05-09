# Testing guide (Laravel 11 / PHPUnit)

This document describes how automated tests are organized for the Compliance Checklist application, how to run them, and what is covered.

---

## Quick commands

Run the full suite:

```bash
php artisan test
```

Filter by class name substring:

```bash
php artisan test --filter=PublicApi
php artisan test --filter=PdfExport
php artisan test --filter=ExportQueue
```

Run only unit tests:

```bash
php artisan test tests/Unit
```

Run only feature tests:

```bash
php artisan test tests/Feature
```

Fresh database with demo seed data (manual / local reset — **not** required for each PHPUnit run):

```bash
php artisan migrate:fresh --seed
```

PHPUnit uses `RefreshDatabase` per test class and `QUEUE_CONNECTION=sync` by default (see `phpunit.xml`). Tests that need the real database queue override `queue.default` locally (see `QueuePdfExportWorkerTest`).

---

## Test architecture

| Layer | Directory | Purpose |
|-------|-----------|---------|
| **Unit** | `tests/Unit` | Fast tests with **no HTTP**: pure services, enums, threshold logic (`ExportQueueDecision`). Uses `RefreshDatabase` when DB state is required. |
| **Feature** | `tests/Feature` | **HTTP + database** integration: routes, controllers, policies, jobs side effects, JSON envelopes. |

**Naming:** Methods use PHPUnit’s `test_*` convention with descriptive snake_case (`test_public_login_rejects_invalid_credentials`).

**Isolation:** Each test class uses `RefreshDatabase`. Tests do not depend on execution order.

**Factories:** Prefer `User::factory()`, `ChecklistTemplate::factory()`, `Export::factory()`, etc., instead of hard-coded IDs.

**Roles:** `Tests\TestCase` seeds Spatie roles **`admin`** and **`auditor`** in `setUp()` so every feature test has consistent RBAC primitives.

---

## Coverage overview

### Authentication & tokens

| Area | Tests |
|------|--------|
| Legacy `/api/v1/auth/*` login + logout | `AuthFeatureTest` |
| Stable `/api/login`, `/api/me`, `/api/logout` | `PublicApiAuthFeatureTest` |

### Role middleware & policies

| Area | Tests |
|------|--------|
| Admin vs auditor ping routes (`/api/v1/*`) | `RolePermissionsFeatureTest` |
| Export ownership (`GET /api/exports/{uuid}`) | `ExportApiFeatureTest` |

### Checklist domain

| Area | Tests |
|------|--------|
| V1 auditor instances (start, answers, complete) | `ChecklistCompletionFeatureTest` |
| Stable `/api/checklists/*` workflow | `PublicApiChecklistWorkflowFeatureTest` |
| Template CRUD + questions (`/api/v1/*`) | `ChecklistTemplateCrudFeatureTest` |
| Stable `/api/templates/*` admin CRUD | `PublicApiTemplatesFeatureTest` |

### Validation

| Area | Tests |
|------|--------|
| Invalid template/question payloads | `ValidationFailuresFeatureTest` |

### Reporting

| Area | Tests |
|------|--------|
| Completed-only instances in admin report query | `ReportingFiltersFeatureTest` |
| Stable `/api/reports` + auditor forbidden | `PublicApiReportsFeatureTest` |

### PDF & exports

| Area | Tests |
|------|--------|
| Web + legacy API PDF routes, sync download | `PdfExportFeatureTest` |
| Queued report dispatch (`POST /api/exports/pdf`) | `PdfExportFeatureTest` |
| Database queue processes `GenerateStoredPdfExportJob` | `QueuePdfExportWorkerTest` |
| Export API list/show + validation | `ExportApiFeatureTest` |
| Job `failed()` updates export row | `GenerateStoredPdfExportJobFailureTest` |

### Threshold logic (unit)

| Area | Tests |
|------|--------|
| `ExportQueueDecision` sync vs queue rules | `ExportQueueDecisionTest` |

---

## Known gaps / non-goals

The following are intentionally light or excluded to keep the suite fast and stable:

- **Visual / browser (Dusk)** — not configured.
- **Every permutation of PDF filters** — covered by representative routes + queue decision unit tests; exhaustive DomPDF output snapshots are not asserted.
- **Redis queue driver** — CI uses `database` or `sync`; Redis-specific behavior is not asserted.
- **Signed URL expiry edge cases** — covered indirectly via API docs and manual checks.

---

## Adding new tests

1. Prefer **feature tests** for new HTTP endpoints or cross-cutting workflows.
2. Use **unit tests** for new **pure** domain rules (no `Http::`, no `$this->getJson`).
3. Reuse factories; add states on factories (`published()`, `failed()`, etc.) instead of duplicating setup.
4. Keep assertions meaningful: status code **plus** key JSON paths or database assertions.
