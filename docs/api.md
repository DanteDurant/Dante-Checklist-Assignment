## Compliance Checklist API (Laravel Sanctum)

### Base URL

- Local: `http://localhost:8000`

All endpoints below are under the `/api` prefix.

### Authentication

This API uses **Bearer tokens** via **Laravel Sanctum**.

- **Login** returns a token.
- Send it on subsequent requests:

`Authorization: Bearer <token>`

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

