# Local Development Admin Account (XAMPP only — NOT for production)

There is NO public admin registration in SPVAI and this file does NOT create one.
Student registration (`register.php` → `data/register.php`) hardcodes `role = 'student'`.
Admins are provisioned directly in the database as described below.

## Role value
- Admin accounts use `users.role = 'admin'` (VARCHAR(20), default `'student'`).
- Authentication: `class/Auth.php :: authenticate()` looks up `users` by email and
  verifies with `password_verify()` against `password_hash` (PHP `password_hash()`,
  bcrypt). `data/login.php` additionally requires `role === 'admin'`.

## Current development account
- Email: `dev.admin@spvai.edu.ph`
- Password (LOCAL DEVELOPMENT ONLY): `SpvaiDev-Admin-2026!`
- Login page (existing, unchanged): `http://localhost/SPVAI/admin/index.php`
- Lands on: `http://localhost/SPVAI/admin/dashboard.php`
- Pre-existing accounts (`admin@spvai.edu.ph`, `migration_test@spvai.edu.ph`) were
  left untouched with unknown passwords.

## How it was provisioned (no application code was changed)
Hash generated locally with the project's own PHP (`password_hash(..., PASSWORD_DEFAULT)`),
then inserted directly via MySQL CLI:

```sql
INSERT INTO spvaii.users (student_id, first_name, last_name, email, phone, password_hash, role)
VALUES (NULL, 'Dev', 'Admin', 'dev.admin@spvai.edu.ph', NULL, '<bcrypt-hash>', 'admin');
```

To rotate the local password, generate a new hash with PHP CLI and update it:

```sql
UPDATE spvaii.users SET password_hash = '<new-bcrypt-hash>'
WHERE email = 'dev.admin@spvai.edu.ph';
```

## Removal (keeps existing accounts intact)
```sql
DELETE FROM spvaii.users WHERE email = 'dev.admin@spvai.edu.ph';
```

## Verified
- Admin login via `/admin/index.php` succeeds and routes to `/admin/dashboard.php`.
- Logged-in admin reaches dashboard/requests (HTTP 200).
- Logged-in student is denied admin pages (302 → `index.php?error=unauthorized`).
- Logged-out admin pages redirect to `index.php` (existing P9-014 behavior).
