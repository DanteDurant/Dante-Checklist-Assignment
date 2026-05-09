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

---

## Authentication

This API uses **Bearer tokens** via **Laravel Sanctum**.

- **Login** returns a token.
- Send it on subsequent requests:

`Authorization: Bearer <token>`

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

---

## Troubleshooting

- **401 Unauthenticated**: you didn’t send `Authorization: Bearer <token>` or token is revoked.
- **403 Forbidden**: you’re authenticated but using the wrong role (admin vs auditor).
- **422 Validation failed**: request body is missing required fields or checklist completion is missing required answers.
- **Cannot start checklist**: template must be `published`.
- **Database/migrations**: run `php artisan migrate --seed`.
- **Wrong port**: update Postman collection variable `base_url` to match `php artisan serve` output.

