- [x] Styling the page using [Bootstrap](http://getbootstrap.com/) or [Tailwind](https://tailwindcss.com/)
- [x] Adding validation of new records
- [x] Creating a JS search functionality that allows users to filter records by city
- [x] Implementing form submission using AJAX
- [x] Adding a phone number column to the table
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

- [x] JS city search/filter functionality
  - [x] Added city search input above user table
  - [x] Implemented real-time filtering as user types
  - [x] Added clear filter button
  - [x] Added no-results row when no city matches query
  - [x] Moved inline page scripts into external `js/application.js` for CSP compatibility

- [x] AJAX form submission
  - [x] Added AJAX submit handler with `fetch` in `js/application.js`
  - [x] Kept progressive enhancement fallback (non-JS still posts to `create.php`)
  - [x] Added JSON response mode in `create.php` for AJAX requests
  - [x] Preserved backend validation + CSRF protection for AJAX and non-AJAX flows
  - [x] Added user-facing success/error feedback area in create form
  - [x] Appends newly created row in table without page refresh

- [x] Phone number column using `intl-tel-input`
  - [x] Added local assets for `intl-tel-input` (JS/CSS/images), no CDN at runtime
  - [x] Added phone input with country flag/prefix selector in create form
  - [x] Frontend validation with `intl-tel-input` (`isValidNumber`) and E.164 sync
  - [x] Added hidden `phone` field sent as E.164 (e.g. `+14155552671`)
  - [x] Backend validation enforces strict E.164 format and length
  - [x] Added `phone` column in table and AJAX row rendering

## Replaced mysqli for pdo
I thought about adding an ORM for this project, but think it would be overkill for our needs.