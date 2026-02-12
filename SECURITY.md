# Security Notes and Attack Scenarios

This document describes the main web attack vectors relevant to this project, how they could be exploited in the original implementation, and how they are mitigated now.

It does not claim to cover every attack ever possible on any PHP app. It focuses on realistic threats for this codebase.

## Scope

- Entry points:
  - `GET /index.php`
  - `POST /create.php`
- User-controlled input:
  - `name`
  - `email`
  - `city`
  - `phone`
- Data sinks:
  - SQL `INSERT` into `users`
  - HTML table rendering
  - Error output and redirects

## 1) SQL Injection (SQLi)

### How it was done before

Before hardening, `create.php` passed raw `$_POST` values to model insert flow that built SQL strings manually. An attacker could inject SQL fragments through form fields.

Example payload:

```text
name: x', NOW()); DROP TABLE users; --
```

Impact:

- Data corruption or deletion
- Unauthorized reads/writes depending on query shape and DB privileges

### Current mitigation

- `create.php` now uses prepared statements:
  - `prepare('INSERT INTO users (...) VALUES (?, ?, ?, ?, NOW())')`
  - `bind_param('ssss', ...)`
- Input validation reduces malicious payload surface before DB layer.

### How to test now

Submit:

```text
name: ' OR 1=1 --
email: test@example.com
city: NYC
```

Expected:

- Record is stored as literal text (or rejected by validation), no SQL behavior change.

## 2) Stored Cross-Site Scripting (Stored XSS)

### How it was done before

Before hardening, user values were rendered directly in `views/index.php` without HTML escaping.

Example payload:

```text
name: <script>alert('xss')</script>
```

Impact:

- JavaScript execution for every viewer of the table
- Session theft, malicious redirects, defacement

### Current mitigation

- All rendered values are escaped with:
  - `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`
- CSP header added in `core/app.php` to reduce script execution risk.

### How to test now

Submit script-like input and reload table.

Expected:

- Payload appears escaped (`&lt;script&gt;...`) and does not execute.

## 3) Reflected XSS via Error/Old Input Rendering

### How it was done before

If validation errors or previously submitted values are printed without escaping, attacker input can execute in the error area.

### Current mitigation

- Error messages and old form values are escaped with `htmlspecialchars(...)`.

### How to test now

Submit invalid form with:

```text
name: <img src=x onerror=alert(1)>
email: invalid
```

Expected:

- Error banner appears.
- Old value is escaped, no JS execution.

## 4) CSRF (Cross-Site Request Forgery)

### How it was done before

Without CSRF protection, another site could force a victim browser to submit `POST /create.php`.

Example attacker page:

```html
<form action="http://localhost:8080/create.php" method="POST">
  <input name="name" value="forged">
  <input name="email" value="forged@example.com">
  <input name="city" value="forged-city">
</form>
<script>document.forms[0].submit()</script>
```

### Current mitigation

- Session token generated in `index.php`.
- Hidden `csrf_token` added to form.
- `create.php` verifies token with `hash_equals(...)`.

### How to test now

- Remove/modify `csrf_token` in DevTools and submit.

Expected:

- Request rejected, no DB write.

## 5) Missing Method Enforcement / Unsafe Request Handling

### How it was done before

`create.php` processed input without strict method checks.

### Current mitigation

- `create.php` now allows only `POST`; all other methods redirect to index.

### How to test now

Run:

```bash
curl -i http://localhost:8080/create.php
```

Expected:

- Redirect (`303`) to `index.php`, no record creation.

## 6) Information Disclosure via Verbose DB Errors

### How it was done before

Database errors were shown with internal details and stack trace, exposing SQL internals and file paths.

Impact:

- Easier targeted exploitation
- Leaks implementation details

### Current mitigation

- Generic user-facing DB errors only.
- Internal DB errors logged using `error_log(...)`.

### How to test now

Force a DB issue (e.g., stop DB container) and load app.

Expected:

- Generic failure message, no SQL trace shown to user.

## 7) Clickjacking and MIME Sniffing Hardening

### Attack idea

- Clickjacking: app embedded in attacker frame to trick clicks.
- MIME sniffing: browser interprets content unexpectedly.

### Current mitigation

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- Plus strict CSP in `core/app.php`.

## 8) Input Validation Bypass Attempts

### Attack idea

Client-side validation can be bypassed by direct HTTP requests.

### Current mitigation

- Full server-side validation in `create.php`:
  - required fields
  - format checks
  - length checks
  - strict phone validation in E.164 format

### How to test now

Use `curl` to send invalid payload directly.

Expected:

- Rejected by backend validation.

## Residual Risks / Not Yet Implemented

These are common production controls not yet added in this assignment:

- Authentication/authorization (app is currently open).
- Rate limiting / anti-automation controls.
- Strong DB schema constraints (`VARCHAR` sizes, unique index for email).
- Audit logging for create events.
- Security monitoring/alerting.
- Automated security tests in CI.

## Quick Smoke-Test Checklist

1. SQLi payload does not alter query behavior.
2. XSS payload is escaped and never executes.
3. Invalid token request is rejected.
4. Non-POST to `/create.php` does not create data.
5. Invalid inputs are blocked server-side.
6. Valid input creates a row.
