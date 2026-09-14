# PHASE 2: USER ACCOUNTS & AUTHENTICATION DOCUMENTATION

This document describes the authentication and identity system implemented in Phase 2.

## 1. Registration Flow
Students can create accounts via `register.php`.
- **Validation**: Server-side validation for Student ID, Email, First/Last Name, and Password match.
- **Uniqueness**: Checks for duplicate Student IDs and Emails before insertion.
- **Security**: Passwords are hashed using `password_hash(..., PASSWORD_DEFAULT)`.

## 2. Login Flow
Authentication is handled via `data/auth_login.php` (Students) and `data/login.php` (Admins).
- **Identifiers**: Email + Password.
- **Verification**: Uses `password_verify()` to check the hash against the stored password.
- **Session**: On success, `session_regenerate_id(true)` is called to prevent session fixation, and `user_id` and `role` are stored in the session.

## 3. Logout Flow
`logout.php` clears all session data, destroys the session, and redirects the user to the landing page.

## 4. Password Security
- **No Plain Text/MD5**: All new accounts use `bcrypt` (via `PASSWORD_DEFAULT`).
- **Admin Migration**: Legacy admin accounts using MD5 are automatically migrated to `password_hash` upon their first successful login through the updated `data/login.php`.

## 5. Session & Role Handling
- **Identity**: The server determines the user's identity via `$_SESSION['user_id']`.
- **Role Protection**: The `Auth` class provides `requireRole($role)` which is integrated into protected pages.
- ** student area**: Protected by `requireRole('student')`.
- ** admin area**: Protected by `requireRole('admin')`.

## 6. CSRF Protection
A simple session-based CSRF token system is implemented in `class/Auth.php`.
- **Token Generation**: `generateCsrfToken()` creates a random token stored in the session.
- **Verification**: `validateCsrfToken($token)` checks POST tokens against the session.
- **Protected Actions**: Registration, Login, and Logout.

## 7. Files Created
- `class/Auth.php`: The primary authentication helper class.
- `register.php`: Student registration UI.
- `login.php`: Student login UI.
- `logout.php`: Session termination.
- `student_area.php`: Secure landing page for authenticated students.
- `data/register.php`: Backend registration handler.
- `data/auth_login.php`: Backend student authentication handler.
- `test_auth.php`: Programmatic verification script.

## 8. Files Modified
- `data/login.php`: Fixed broken instantiation and added MD5 migration logic for admins.
- `admin/index.php`: Updated to use the new CSRF token and `Auth` logic.
- `admin/session_login.php`: Updated to use `requireRole('admin')`.

## 9. Testing Performed
- **Registration**: Verified successful account creation and duplicate prevention (ID and Email).
- **Login**: Verified correct password success and wrong password failure.
- **Session**: Verified `session_regenerate_id` and role-based redirection.
- **Logout**: Verified session destruction.
- **Role Separation**: Verified students cannot access admin pages.
- **Admin Migration**: Verified legacy MD5 admins are migrated to the new `users` table upon login.

## 10. Legacy Authentication
The legacy `user` table is still present for backward compatibility but is being drained as admins are migrated. The new `users` table is the source of truth for all new accounts.

## 11. Known Issues
- None critical. The legacy booking system remains untouched and operational.
