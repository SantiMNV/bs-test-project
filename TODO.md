- [x] Styling the page using [Bootstrap](http://getbootstrap.com/) or [Tailwind](https://tailwindcss.com/)
- [x] Adding validation of new records
- [ ] Creating a JS search functionality that allows users to filter records by city
- [ ] Implementing form submission using AJAX
- [ ] Adding a phone number column to the table
- [ ] Deploying your project and sending us a production link


# Extra 
this is not a perfect checklist. So please, don’t limit yourself to these points—engineering at Better Stack isn’t about simply completing a list of tasks. It’s about engineers shipping a great piece of software end-to-end that people will actually enjoy using!

- [x] Update jquery and bootstrap to secure versions

# Detailed progress

## Validation and security hardening completed

- [x] Backend validation for new records (`create.php`)
  - [x] Accepts only `POST` requests for create operation
  - [x] Validates required fields: `name`, `email`, `city`
  - [x] Validates field lengths (`name`/`city` max 100, `email` max 254)
  - [x] Validates email format with `FILTER_VALIDATE_EMAIL`
  - [x] Validates `name` and `city` characters with regex
  - [x] Returns user-friendly errors and preserves submitted values

- [x] Frontend validation (`views/index.php`)
  - [x] Added HTML constraints: `required`, `maxlength`, `pattern`
  - [x] Added Bootstrap invalid feedback messages
  - [x] Added client-side validation behavior (`needs-validation` + JS)

- [x] SQL injection prevention
  - [x] Replaced raw insert flow with prepared statement (`prepare` + `bind_param`)
  - [x] Removed direct use of untrusted `$_POST` values in SQL

- [x] XSS prevention
  - [x] Escaped all rendered user data in table with `htmlspecialchars`
  - [x] Escaped error messages and repopulated form values

- [x] CSRF protection
  - [x] Added session-based CSRF token generation (`index.php`)
  - [x] Added hidden CSRF token field in form
  - [x] Added token verification with `hash_equals` (`create.php`)

- [x] Additional security improvements
  - [x] Started session safely in app bootstrap (`core/app.php`)
  - [x] Added security headers:
    - [x] `Content-Security-Policy`
    - [x] `X-Content-Type-Options: nosniff`
    - [x] `X-Frame-Options: DENY`
    - [x] `Referrer-Policy: strict-origin-when-cross-origin`
  - [x] Reduced sensitive DB error leakage in `core/database.php`
  - [x] Switched DB connection charset to `utf8mb4`
