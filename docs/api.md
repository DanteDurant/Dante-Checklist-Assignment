## Compliance Checklist API (Laravel Sanctum)

### Who this is for

This guide is written for evaluators who want to quickly verify the API works end-to-end using Postman (no prior project context assumed).

### Base URL

- Local: `http://localhost:8000`

All endpoints below are under the `/api` prefix.

---

## Quick start (recommended evaluator flow)

1. Start the server: `php artisan serve`
2. In Postman: import the collection at `postman/Compliance-Management-System.postman_collection.json`
3. Login as **Admin** → create a template → add questions
4. Login as **Auditor** → start checklist → save draft → complete
5. Login as **Admin** → run reports filter endpoint

---

## Postman step-by-step

### 1) Import

- Postman → **Import** → select `postman/Compliance-Management-System.postman_collection.json`

### 2) Set collection variables

Collection variables:
- `base_url`: set to your running server (e.g. `http://127.0.0.1:8000`)
- `token`: auto-filled after login
- `template_id`: auto-filled after “Create template”
- `question_id`: auto-filled after “Create question”
- `checklist_id`: auto-filled after “Start checklist”
- `export_uuid`: auto-filled when **Create PDF export** returns **202**
- `download_url`: auto-filled when **Get export by UUID** returns a completed export with `download_url`

### 3) Login and verify token

Run: **Auth → Login (Admin)** (or “Login (Auditor)”)

Expected:
- Response contains `data.token`
- Collection variable `token` is set automatically

Then run: **Auth → Me**

Expected:
- `success: true`
- `data.roles` contains `admin` or `auditor`

### 4) Admin flow (templates + questions + reports)

1. **Templates (Admin) → Create template**
2. **Questions (Admin) → Create question (choice)**
3. **Reports (Admin) → Reports (filters)**

Notes:
- Auditors cannot access Admin endpoints (you’ll get 403 if logged in as auditor).

### 5) Auditor flow (start + draft + complete)

1. **Auth → Login (Auditor)**
2. Ensure `template_id` points to a **published** template (auditors can only start published templates).
3. **Checklists (Auditor) → Start checklist** (auto-fills `checklist_id`)
4. **Checklists (Auditor) → Save draft**
5. **Checklists (Auditor) → Complete checklist**

Expected:
- If required answers are missing/empty, complete returns **422** with `errors`.
- Once completed, the checklist is immutable (attempts to save again fail).

### 6) PDF exports (optional — sync or queued)

1. Stay logged in (collection variable `token` set).
2. Open folder **Exports (PDF System)**.
3. Run **Create PDF export (unified)** — you may get **200** with a PDF (small export) or **202** JSON (queued export).
4. If you get **202**, copy `export_uuid` (saved automatically to collection variable `export_uuid`) and run **Get export by UUID** until `status` is `completed`.
5. Open **Download completed PDF** — uses `download_url` from the previous response (saved to `download_url`). Ensure **`php artisan queue:work`** is running if exports stay `queued` (see [Troubleshooting](#troubleshooting)).

---

## Authentication

This API uses **Bearer tokens** via **Laravel Sanctum**.

- **Login** returns a token.
- Send it on subsequent requests:

```http
Authorization: Bearer YOUR_TOKEN
```

Replace `YOUR_TOKEN` with the plain-text token from `data.token` in the login response.

All **export** endpoints below require this header unless noted otherwise.

### Token lifecycle

- Tokens are long-lived until revoked.
- **Logout** revokes tokens for the current user (invalidating further requests).

---

## Response envelope

### Success

```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

### Validation error (HTTP 422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## Auth

### POST `/api/login`

Body:

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "postman"
}
```

Response `200`:

```json
{
  "success": true,
  "message": "Logged in",
  "data": {
    "token": "…",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "roles": ["admin"],
      "permissions": []
    }
  }
}
```

### POST `/api/logout`

Headers: `Authorization: Bearer <token>`

Response `200`:

```json
{ "success": true, "message": "Logged out", "data": null }
```

### GET `/api/me`

Headers: `Authorization: Bearer <token>`

---

## Templates (admin)

### GET `/api/templates`

Query:
- `per_page` (1–100)

### POST `/api/templates`

```json
{ "title": "ISO 27001", "description": "…", "status": "published" }
```

### GET `/api/templates/{template}`
### PUT `/api/templates/{template}`
### DELETE `/api/templates/{template}`

---

## Questions (admin)

### POST `/api/templates/{template}/questions`

```json
{
  "question_text": "Do you encrypt backups?",
  "answer_type": "boolean",
  "required": true,
  "sort_order": 10,
  "options": [
    { "value": "yes", "label": "Yes" },
    { "value": "no", "label": "No" }
  ]
}
```

Notes:
- `options` is only relevant for `select`, `radio`, `checkbox` (and compatible `single_select`, `multi_select`).

### PUT `/api/questions/{question}`
### DELETE `/api/questions/{question}`

---

## Checklists (auditor)

### GET `/api/checklists`

Query:
- `per_page` (1–100)

### POST `/api/checklists/start/{template}`

Starts a new checklist instance for the authenticated auditor.

### GET `/api/checklists/{checklist}`

### PUT `/api/checklists/{checklist}/save-draft`

Body shape matches the Blade form:

```json
{
  "answers": {
    "123": "Some text",
    "124": 10,
    "125": "2026-05-09",
    "126": ["a", "b"]
  }
}
```

### PUT `/api/checklists/{checklist}/complete`

Completes the checklist (immutable afterwards). Returns 422 if required answers are missing/empty.

---

## Reports (admin)

### GET `/api/reports`

Filters:
- `date_from` (date)
- `date_to` (date, after_or_equal date_from)
- `template_id` (int)
- `auditor_id` (int)
- `q` (string search)
- `per_page` (1–100)

---

## PDF Export System (Synchronous & Asynchronous)

This application can produce PDF exports in two ways:

| Mode | What happens | Typical HTTP response |
|------|----------------|------------------------|
| **Immediate (synchronous)** | The server builds the PDF during the request and returns the file. | **200** with `Content-Type: application/pdf` |
| **Background (queued)** | The server saves an **export job** and builds the PDF in a **queue worker**. The client does **not** wait for the file. | **202** with JSON instructions (poll until ready) |

### Why use a queue?

Generating PDFs can use a lot of memory and CPU. Large reports or “dense” detail levels would otherwise slow down the web server and tie up the browser. Queueing keeps HTTP responses fast and the UI responsive: users get an acknowledgement immediately and fetch the file when it is ready.

### When is an export immediate vs queued?

The server applies **threshold rules** (record counts, question counts, detail level, etc.). If the export is “small enough,” it runs inline. If it crosses a threshold, it is **queued**.

Thresholds live in `config/pdf_exports.php` and can be overridden with environment variables such as `PDF_EXPORT_SYNC_MAX_REPORT_ROWS`, `PDF_EXPORT_SYNC_MAX_CHECKLIST_QUESTIONS`, and related keys.

**Important:** Users are **not** meant to wait on the HTTP request for large PDFs. For queued exports, poll status or come back later using the download link.

---

### User flow (step-by-step)

#### A. Creating an export

1. The user triggers an export from the **web UI** or calls **`POST /api/exports/pdf`** (or a legacy `GET …/export-pdf` URL).
2. The server evaluates the **filters**, **detail level**, and **dataset size** (counts).
3. The server chooses **immediate** or **queued** mode.

#### B. Immediate export

1. The PDF is generated during the request.
2. The client receives **200** and a PDF body (download starts).

#### C. Queued export

1. An **export record** is created and a **job** is dispatched to Laravel’s queue (`QUEUE_CONNECTION` in `.env`, commonly `database`).
2. Status starts as **`queued`**, then moves to **`processing`** while the worker runs.
3. When generation succeeds, status becomes **`completed`** and a **`download_url`** (signed, time-limited URL) is available.
4. If generation fails after retries, status is **`failed`** and an error message is stored.

Poll **`GET /api/exports/{uuid}`** (or the web status URL from the **202** response) until `status` is `completed` or `failed`.

---

### Export lifecycle statuses

| Status | Meaning | What the client should do |
|--------|---------|---------------------------|
| `queued` | Job is waiting for a worker | Poll again after a short delay |
| `processing` | Worker is generating the PDF | Poll again |
| `completed` | File is stored; download allowed | Use `download_url` when present |
| `failed` | Generation failed | Show `error` message; user may retry a new export |

---

### Authentication (exports)

All endpoints in this section require:

```http
Authorization: Bearer YOUR_TOKEN
```

Use the token from **`POST /api/login`**. Roles apply as follows:

- **`POST /api/exports/pdf`** — **Admin or Auditor** (`role:admin|auditor`), but each `export_type` still enforces rules (e.g. only admins can run org-wide reports).
- **`GET /api/exports`** and **`GET /api/exports/{uuid}`** — **Admin or Auditor**; users normally see **their own** exports (admins may pass **`?all=1`** on the list endpoint).

---

### New unified JSON API (recommended for evaluators)

#### `POST /api/exports/pdf`

**Purpose:** Create a PDF export. Response is either an immediate PDF or a queued job description.

**Headers:**

```http
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json, application/pdf
```

**Body shape:**

```json
{
  "export_type": "<see table below>",
  "filters": { }
}
```

**`export_type` values** (string):

| `export_type` | Who typically uses it | Required `filters` fields (minimum) |
|---------------|-------------------------|--------------------------------------|
| `checklist_instance` | Admin or Auditor | `checklist_instance_id`, optional `detail`, `sections` |
| `checklist_report` | Admin | Same filters as `GET /api/reports`, optional `detail` |
| `checklist_template` | Admin | `checklist_template_id`, optional `detail` |
| `compliance_snapshot` | Admin | optional `date_from`, `date_to`, `detail` |
| `auditor_activity` | Admin or Auditor | optional dates, `detail`; use `filters.auditor_scope`: `"admin"` (full report, admin only) or `"self"` (my activity, auditors) |

**Example — compliance snapshot (admin):**

```json
{
  "export_type": "compliance_snapshot",
  "filters": {
    "detail": "standard",
    "date_from": null,
    "date_to": null
  }
}
```

**Responses:**

**200 — Immediate PDF**

- Headers include `Content-Type: application/pdf`.
- Body is raw PDF bytes.

**202 — Queued**

```json
{
  "success": true,
  "message": "Your export is being prepared.",
  "data": {
    "async": true,
    "export_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "queued",
    "status_url": "http://localhost:8000/exports/550e8400-e29b-41d4-a716-446655440000/status"
  }
}
```

Poll **`GET /api/exports/{export_uuid}`** until completed (or follow `status_url` from the browser session).

---

#### `GET /api/exports`

**Purpose:** List recent exports for the current user (status overview).

**Query:**

- `all` — optional; if `1` and the user is an **admin**, listing includes a broader recent set (still capped server-side).

**Example:**

```http
GET /api/exports
Authorization: Bearer YOUR_TOKEN
```

**Example response (200):**

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "exports": [
      {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "export_type": "checklist_report",
        "status": "completed",
        "created_at": "2026-05-09T12:00:00+00:00",
        "completed_at": "2026-05-09T12:00:05+00:00",
        "download_url": "http://localhost:8000/exports/550e8400-e29b-41d4-a716-446655440000/download?expires=…&signature=…"
      }
    ]
  }
}
```

`download_url` appears only when `status` is `completed` and a file exists.

---

#### `GET /api/exports/{uuid}`

**Purpose:** Single export detail — status, filters snapshot, optional error, optional download URL.

```http
GET /api/exports/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer YOUR_TOKEN
```

**Example response (200) when completed:**

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "export_type": "compliance_snapshot",
    "status": "completed",
    "filters": { "detail": "standard", "date_from": null, "date_to": null },
    "created_at": "2026-05-09T12:00:00+00:00",
    "completed_at": "2026-05-09T12:00:05+00:00",
    "filename": "compliance-snapshot-standard-2026-05-09.pdf",
    "error": null,
    "download_url": "http://localhost:8000/exports/550e8400-e29b-41d4-a716-446655440000/download?expires=…&signature=…"
  }
}
```

**Downloading the file:** send **GET** to `download_url` with the **same** `Authorization: Bearer YOUR_TOKEN`. The URL includes a Laravel **signature** query string; do not strip it.

---

### Legacy PDF routes (`GET`, direct download)

These endpoints still work and use the **same** sync vs queued rules as the UI. When queued, they return **202** JSON (not a PDF) — treat like **`POST /api/exports/pdf`**.

**Detail levels** (query: `detail=`):

- `summary` — minimal portfolio / instance overview  
- `standard` — default balanced report (default if omitted)  
- `detailed` — audit-grade: extra metadata, raw values, timeline (where applicable)  
- `executive` — KPIs, status mix, short previews (large tables may be capped)

**Admin-only**

- `GET /api/reports/export-pdf` — completed checklist register; same filters as `GET /api/reports` plus `detail`
- `GET /api/reports/compliance-snapshot/export-pdf` — dashboard snapshot; optional `date_from`, `date_to`, `detail`
- `GET /api/reports/auditor-activity/export-pdf` — workload report; optional `date_from`, `date_to`, `auditor_id`, `detail`
- `GET /api/templates/{template}/export-pdf` — template specification; optional `detail`

**Admin or auditor** (checklist must be completed and exportable)

- `GET /api/checklists/{checklist}/export-pdf` — single instance; optional `detail` and `sections` (comma-separated: `metadata`, `metrics`, `responses`, `timeline`, `findings`, `toc`)

**Examples**

```http
GET /api/reports/export-pdf?detail=executive&template_id=1
Authorization: Bearer YOUR_TOKEN
```

```http
GET /api/checklists/42/export-pdf?detail=detailed
Authorization: Bearer YOUR_TOKEN
```

---

### Frontend (Blade) behavior

- **Small exports** — the browser receives a PDF immediately (same session cookie).
- **Large exports** — JavaScript receives **202 JSON**, shows a **“Preparing…”** style state, **polls** status, then navigates to **`download_url`** when ready (no full-page hang).
- Users can **leave and return**; exports are tied to the user and listed via **`GET /api/exports`**.
- The UI should **never** spin forever: polling has timeouts; failures surface as alerts.

---

## Endpoint reference (at a glance)

### Auth
- `POST /api/login` (public)
- `POST /api/logout` (auth)
- `GET /api/me` (auth)

### Templates (admin, auth + role:admin)
- `GET /api/templates`
- `POST /api/templates`
- `GET /api/templates/{template}`
- `PUT /api/templates/{template}`
- `DELETE /api/templates/{template}`

### Questions (admin, auth + role:admin)
- `POST /api/templates/{template}/questions`
- `PUT /api/questions/{question}`
- `DELETE /api/questions/{question}`

### Checklists (auditor, auth + role:auditor)
- `GET /api/checklists`
- `POST /api/checklists/start/{template}`
- `GET /api/checklists/{checklist}`
- `PUT /api/checklists/{checklist}/save-draft`
- `PUT /api/checklists/{checklist}/complete`

### Reports (admin, auth + role:admin)
- `GET /api/reports`

### PDF exports (admin)
- `GET /api/reports/export-pdf`
- `GET /api/reports/compliance-snapshot/export-pdf`
- `GET /api/reports/auditor-activity/export-pdf`
- `GET /api/templates/{template}/export-pdf`

### PDF exports (admin or auditor)
- `GET /api/checklists/{checklist}/export-pdf`

### PDF exports — unified JSON API (admin or auditor)
- `POST /api/exports/pdf`
- `GET /api/exports`
- `GET /api/exports/{uuid}`

---

## Troubleshooting

### Auth and roles

- **401 Unauthenticated**: you didn’t send `Authorization: Bearer YOUR_TOKEN`, the header is misspelled, or the token was revoked (logout).
- **403 Forbidden**: you’re authenticated but the role is wrong for the route (admin-only vs auditor-only), or you’re trying to access another user’s export.

### Validation

- **422 Validation failed**: body/query is missing fields or filters are invalid for the chosen `export_type`.

### Checklists

- **Cannot start checklist**: template must be `published`.
- **Cannot export checklist PDF**: instance must be completed/submitted per policy (same rules as before).

### Database

- **Database/migrations**: run `php artisan migrate` (and `php artisan migrate --seed` for demo data).

### Postman / base URL

- **Wrong port or host**: set collection variable `base_url` to match `php artisan serve` (for example `http://127.0.0.1:8000`).

### Queued PDF exports

- **Export stuck in `queued` or `processing`**: the **queue worker is not running**. In `.env`, ensure `QUEUE_CONNECTION=database` (or `redis`), run migrations so the `jobs` table exists, then start a worker:

```bash
php artisan queue:work
```

Stop with `Ctrl+C` when finished in development. In production, use **Supervisor** or your platform’s worker service.

- **`failed` status**: DomPDF may have hit memory/time limits, or data became invalid. Check `storage/logs/laravel.log`. Retry with a narrower date range, lower **detail** level, or fewer filters.

- **No `download_url` yet**: status is not `completed`. Poll **`GET /api/exports/{uuid}`** until it appears, or fix the queue worker first.

- **Download URL returns 403**: signature expired (URLs are short-lived) — call **`GET /api/exports/{uuid}`** again for a fresh `download_url`.

- **Download URL returns 401**: include **`Authorization: Bearer YOUR_TOKEN`** on the GET request to `download_url`.

