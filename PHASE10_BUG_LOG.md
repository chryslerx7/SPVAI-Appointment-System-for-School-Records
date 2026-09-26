# Phase 10 Bug/Task Log

This file records Phase 10 Final Integration & Production Preparation work,
following the same format as PHASE9_BUG_LOG.md.

## Bug Format
- **ID**: [P10-XXX]
- **Description**: Brief description.
- **Severity**: [CRITICAL | HIGH | MEDIUM | LOW | COSMETIC]
- **Root Cause**: Why it is happening (for fixes).
- **Fix**: How it was fixed.
- **Retest**: Result of retesting.
- **Status**: [OPEN | FIXED | VERIFIED]

---
## P10-002 — Public landing page
- **Description**: Root `/SPVAI/` sent every logged-out visitor straight to `login.php`; there was no public entry point explaining the Records Office system.
- **Severity**: Medium (missing Phase 10 entry point; no data/auth impact)
- **Root Cause**: `index.php` else-branch unconditionally redirected logged-out users to `login.php`.
- **Fix**: `index.php` now renders a public Digital Brutalism landing page (brand, description, Get Started → `login.php`, Student Login → `login.php`, Register → `register.php`, subtle Admin Login → `admin/index.php`) for logged-out visitors only. Authenticated student/admin routing lines left byte-identical. No new routes, no auth/RBAC/CSRF changes, no schema changes, no legacy changes, no JavaScript added.
- **Retest**: `php -l` clean + full-project sweep zero errors; logged-out `/` → 200 landing (all CTAs resolve to live 200 pages); logged-in student `/` → 302 `student_area.php`; logged-in admin `/` → 302 `admin/dashboard.php`; student→admin still denied; refresh stable. Temp student used for routing proof removed afterward (users back to 5).
- **Status**: FIXED

---
## P10-003 — Responsive Student/Admin UI
- **Description**: Fixed `text-5xl` page headings overflowed/touched borders at 390px on both portals; admin sidebar was static stacked with no hamburger.
- **Severity**: Medium (usability; no data/auth impact)
- **Student fixes**: all 10 page headings → `text-4xl md:text-5xl` (desktop identical); request-details header wraps; profile avatar `shrink-0` + name `min-w-0`/`break-words`/`text-2xl md:text-3xl`; email boxes `break-words` (profile, dashboard); notifications meta row wraps; confirmation detail rows gain `gap-4`; greeting `break-words`.
- **Admin fixes**: all 6 page headings scale identically; 3 filter-bar control groups wrap; request-details info rows gain `gap-4`; admin email `break-words`. All 4 data tables already scrolled via `overflow-x-auto` (verified, untouched).
- **Admin hamburger**: mirrors the P9-015 student model — dark `md:hidden` top bar + yellow hamburger (`aria-expanded`/`aria-controls`/label), off-canvas `admin-sidebar` (`max-w-[85vw]`, slide transition), backdrop, close button, Escape, link-close, scroll-lock, focus return, resize reset, `__spvaiAdminNav` double-bind guard, logout pinned bottom via `mt-auto`. Desktop static sidebar preserved via `md:` overrides.
- **Retest**: `php -l` clean on both layouts + full-project sweep zero errors; admin nav script 15/15 headless behavior checks; logged-out guards intact on both portals; class audit confirms no fixed headings/static sidebars/unwrapped filter rows remain. No live browser here — DevTools pass at 390/768/1440 recommended.
- **Limitations**: No optical verification of rendered pixels; numeric `text-6xl`/`text-8xl` stat figures intentionally left full-size (short centered numerals).
- **Status**: FIXED

---
## P10-005 — Student Appointments Navigation
- **Description**: Student sidebar "Appointments" pointed to bare `appointments.php` with no `?id=`, which immediately JS-bounced to `my_requests.php?error=no_request`. There is no general appointments list behind `appointments.php`, so the sidebar item was a dead end (P10-004 HIGH finding, P10-001 carry-over).
- **Severity**: High (navigation dead end; no data/auth impact)
- **Root Cause**: `appointments.php` is a per-request scheduler requiring `?id=<request_id>` plus ownership/eligibility checks; the sidebar exposed it as a global section with no list view behind it.
- **Fix**: `layouts/student_header.php` only — the "Appointments" anchor `href` changed from `appointments.php` to `my_requests.php`. Label, styling classes, sidebar position, hamburger/backdrop/close behavior, and `appointments.php` itself left untouched. No new page, no new queries, no business-logic, auth, or schema change.
- **Resulting navigation**: Appointments → `my_requests.php` → per-row Schedule (eligible requests only) → `appointments.php?id=<request_id>` → `appointment_confirmation.php?id=<appointment_id>`. Ineligible requests correctly show no Schedule button; direct `appointments.php` guards/ownership unchanged.
- **Retest**: `php -l layouts/student_header.php` clean + full-project `php -l` sweep zero errors; `git status`/`git diff` confirm only `layouts/student_header.php` + `PHASE10_BUG_LOG.md` changed; sidebar markup verified (Appointments href now `my_requests.php`, My Requests unchanged, P10-003 responsive classes intact); `appointments.php` untouched (scheduler + `?id=` flow preserved); logged-out guard (`requireRole('student')`) and ownership checks untouched. No live browser here — click-through at 390/768/1440 recommended.
- **Status**: FIXED

---
## P10-006 — Remaining Phase 10 Audit (AUDIT ONLY, no code changes)

P10-005 regression verified intact: `layouts/student_header.php:76` Appointments href = `my_requests.php`; `my_requests.php:83` Schedule still points to `appointments.php?id=`; `appointments.php` ownership/eligibility guards and `requireRole('student')` unchanged; responsive sidebar markup unchanged. Full-project `php -l` sweep: zero errors. Live fees read-only: SF10 0.00 / COE 100.00 / COG 0.00 / EMOI 0.00.

### P10-004-02 — Profile "Update Information" button
- **ID**: P10-004-02
- **Title**: Profile "Update Information" button is a dead control
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Low
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed dead control; intentional-vs-unfinished is a business/design decision (read-only profile vs editable profile)
- **Affected File(s)**: `profile.php:43-47`
- **Evidence**: Bare `<button>` with no `type` submit target, no enclosing `<form>`, no `onclick`/`href`/`action`, and no listener in `profile.php` or `layouts/student_footer.php`. Repo-wide search for `update.*profile`, `data/update_profile`, and `Update Information` returns only that button — no backend endpoint exists.
- **Root Cause**: Read-only profile page shipped with an unwired affordance; edit flow was never implemented.
- **Impact**: Clicking produces no visible action; students cannot self-correct phone/name. No data corruption (nothing is submitted).
- **Recommended Future Fix**: Either remove/relabel the button (smallest) or add a profile-update form + POST handler with auth, CSRF, ownership, and validation. Do not do both silently — needs a design decision.
- **Implementation Required**: No (audit only)
- **Notes**: Page copy "Manage your account and contact information." overpromises while read-only. F-05 null-guard gap on the same page (`profile.php:4` no `!$user` check) was noted in P10-004 and remains out of scope here.

### P10-004-03 — min_advance_days enforcement
- **ID**: P10-004-03
- **Title**: `min_advance_days = 1` configured but never enforced
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Medium
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed unused configuration; whether 1-day advance should be policy is a business-rule decision
- **Affected File(s)**: `config/appointments.php:18`, `data/get_slots.php:21-34`, `data/save_appointment.php:58-77`, `appointments.php` (date input, no `min` attribute)
- **Evidence**: Config defines `scheduling_rules.min_advance_days = 1`; repo-wide search shows zero readers of `min_advance_days`. `get_slots.php` enforces only past-date + `allowed_days` Mon–Fri. `save_appointment.php` validates ownership/status/capacity but never date (no past/weekday/advance recheck). Client date input has no `min` attribute.
- **Root Cause**: Rule added to config without wiring into slot listing or save handler.
- **Impact**: Same-day (and via direct POST, past/weekend) dates are obtainable despite stated policy; policy is unenforceable as written.
- **Recommended Future Fix**: Enforce `min_advance_days` in both `get_slots.php` (hide/early dates) and `save_appointment.php` (server-side reject), reading the value from config (no hardcoded `+1`). Keep the config value unchanged until the business confirms it.
- **Implementation Required**: No (audit only)
- **Notes**: Fix must cover both endpoints; client-side `min` alone is insufficient.

### P10-004-04 — data/get_slots.php authentication
- **ID**: P10-004-04
- **Title**: `data/get_slots.php` reachable without login (availability enumeration)
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Low
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed missing auth on this endpoint; whether public availability was intended is undocumented — treat as hardening, not incident
- **Affected File(s)**: `data/get_slots.php:1-16`
- **Evidence**: No `isLoggedIn()`/`requireRole`/CSRF check (unlike sibling POST handlers `save_appointment.php:12-23`, `submit_payment.php:11-22`, `mark_read.php`). Returns per-slot `{time, display_time, remaining, is_full}` counts for any posted date. No student-specific or sensitive fields returned (aggregate counts only, no names/ids).
- **Root Cause**: Endpoint treated as a public utility; auth gate omitted.
- **Impact**: Unauthenticated availability enumeration only; no read of private records, no write path. Actual risk is low.
- **Recommended Future Fix**: Add a JSON auth guard (`isLoggedIn` → `{"valid":false,"msg":"Authentication required."}`) mirroring `save_appointment.php`, then re-test the scheduler flow. CSRF is less relevant for this read-only POST but harmless to keep consistent; do not change slot logic.
- **Implementation Required**: No (audit only)
- **Notes**: Adding auth should not break the scheduler since the page itself already requires a student session.

### P10-004-05 — Payment Center behavior for zero-fee documents
- **ID**: P10-004-05
- **Title**: Zero-fee documents excluded from Payment Center; details page still links into it
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Low
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed behavior; whether zero-fee documents should skip payment entirely is a business decision (fee policy), not just a code bug
- **Affected File(s)**: `payments.php:6-12,23-30`, `request_details.php:145-150`, `data/submit_payment.php:36-56`
- **Evidence**: `payments.php` gate `WHERE r.user_id = ? AND dt.fee > 0`. Live fees: SF10/COG/EMOI 0.00 → hidden, showing "No Payments Due" + Request-a-Document CTA. `request_details.php` for a 0.00 request still renders "No payment submitted yet. Amount due: ₱0.00" + "Go to Payment Center" link into that empty state. No payment record is created for zero-fee requests (only `submit_payment.php` creates rows, reachable only via the gated list). Request completion is unaffected (admin status flow does not require payment).
- **Root Cause**: Fee-gated list combined with zeroed seed/owner fees plus an unconditional payment CTA on the details page.
- **Impact**: Confusing loop for zero-fee requesters ("Amount due ₱0.00" → empty Payment Center); no incorrect charge, no blocked completion.
- **Recommended Future Fix**: Business first: confirm which documents should carry fees. Then either set real fees via `admin/documents.php`, or conditionally hide/retarget the payment CTA when `fee = 0` (e.g., "No payment required" state). Do not change fee values in an audit/fix without owner approval.
- **Implementation Required**: No (audit only)
- **Notes**: Admin payment screens unaffected (they operate on existing payment rows).

### P10-004-06 — Paid payment resubmission behavior
- **ID**: P10-004-06
- **Title**: `submit_payment.php` allows overwriting any existing payment back to Pending Verification
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Medium
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed logic bug (server-side, not just UI)
- **Affected File(s)**: `data/submit_payment.php:43-57`, `payments.php:59-71` (UI)
- **Evidence**: Handler fetches existing payment by `request_id` only (`SELECT payment_id ...`), with no status predicate, then unconditionally `UPDATE ... SET status='Pending Verification'`. UI hides the Submit button unless status is Unpaid/Rejected, but direct POST bypasses that (ownership + CSRF only). A `Paid` row can therefore be flipped back to Pending Verification with a new reference/method.
- **Root Cause**: Missing state-transition guard on the update path.
- **Impact**: Student can un-verify a verified payment, forcing re-verification workload and undermining Paid finality. No cross-user effect (ownership check intact).
- **Recommended Future Fix**: Minimal server-side guard: on the update path, reject unless current `payment_status IN ('Unpaid','Rejected')` (exact set is a business decision); leave the insert path unchanged. No UI change required beyond the existing button gating.
- **Implementation Required**: No (audit only)
- **Notes**: Verify against desired resubmission policy (e.g., whether Refunded should be resubmittable) before implementing.

### P10-004-07 — Missing email templates
- **ID**: P10-004-07
- **Title**: Three emitted notification email keys have no dedicated template (generic fallback)
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Low
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed gap; content of the missing templates is a business decision
- **Affected File(s)**: `class/NotificationService.php:95-128`, `admin/update_status.php:68-75`, `admin/update_appointment.php:61-77`, `admin/update_payment.php:64-80`
- **Evidence**: `getTemplate()` defines 7 keys: request_approved/rejected/processing/ready/completed, appointment_confirmed, payment_verified. Emitted email keys: `request_cancelled` (`update_status.php:74`), `appointment_cancelled` (`update_appointment.php:65`), `payment_rejected` (`update_payment.php:69`) — all three miss and hit the fallback `['subject' => 'SPVAI Notification', 'body' => 'Message regarding your request.']` (`NotificationService.php:127`). (`payment_paid` in-app type is safe only because its email key aliases to existing `payment_verified`.)
- **Root Cause**: Template map not extended when Cancel/Reject notification types were added.
- **Impact**: In-app notifications still created (business transaction unaffected — `notifyUser` creates in-app first, email soft-fails via `@mail` + log per P9-021); emails for those three transitions are generic. No errors thrown (fallback return, no exception).
- **Recommended Future Fix**: Add the three dedicated templates reusing surrounding phrasing/placeholders (`ref`, `remarks`/`date`/`time`/`amount`/`method` as appropriate). No handler logic change needed.
- **Implementation Required**: No (audit only)
- **Notes**: Email delivery itself still depends on SMTP configuration; templates and transport are separate issues.

### P10-004-08 — Admin remarks containing & and = characters
- **ID**: P10-004-08
- **Title**: Admin remarks POST built by raw string concatenation without URL-encoding
- **Status**: OPEN (audit finding, not fixed)
- **Severity**: Medium
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed unsafe encoding step by code inspection (live destructive repro intentionally not performed)
- **Affected File(s)**: `admin/request_details.php:201-204`
- **Evidence**: `var formData = $(this).serialize(); formData += '&remarks=' + $('#admin-remarks').val();` — the textarea sits outside the serialized form, and its raw value is appended without `encodeURIComponent`. A remark like `Bring ID & receipt = required` splits into extra `&`-delimited parameters / truncates at `&` and `=` on the backend (`update_status.php:28` reads `$_POST['remarks']`). Display layer is correctly escaped (`request_details.php` uses `htmlspecialchars`), so this is a transport corruption issue, not an XSS/display issue.
- **Root Cause**: Manual query-string concatenation instead of encoded append or in-form field.
- **Impact**: Admin remarks containing `&`, `=`, `+` (and similar) save truncated/corrupted; students then see the corrupted text. Data already saved this way stays corrupted.
- **Recommended Future Fix**: Smallest: `formData += '&remarks=' + encodeURIComponent($('#admin-remarks').val());` (or move the textarea inside the form so `serialize()` handles it). No backend change needed.
- **Implementation Required**: No (audit only)
- **Notes**: Same-file `update_payment.php`/`update_appointment.php` flows were not in the stated scope; check them for the same pattern before a future fix.

### P10-004-09 — Stray data/create_request.php`` file
- **ID**: P10-004-09
- **Title**: Stray duplicate file `data/create_request.php`` (trailing backtick)
- **Status**: OPEN (audit finding, not removed per preserve rule)
- **Severity**: Very low (hygiene)
- **Confirmed / Not Reproducible / Intentional / Business Decision**: Confirmed exists; dead code, not intentional functionality
- **Affected File(s)**: `data/create_request.php`` (2439 B, same size as `data/create_request.php`)
- **Evidence**: Directory listing of `data/` shows both `create_request.php` and `create_request.php``. Repo-wide reference search for `create_request` finds only `data/create_request.php` (form AJAX in `request_document.php:101`, docs, comment) — nothing references the backtick name. `php -l` was run on the live handler only; the stray was left untouched.
- **Root Cause**: Accidental editor/backup save with a trailing backtick.
- **Impact**: None at runtime; confusion risk (editing the wrong copy).
- **Recommended Future Fix**: Per PRESERVE → INSPECT → TEST → VERIFY → DOCUMENT → APPROVE → REMOVE: removal is safe contingent on owner approval. Verify byte-identity/diff, confirm zero references (done above), then delete only the backtick file in a dedicated cleanup task.
- **Implementation Required**: No (audit only)
- **Notes**: DO NOT delete during any audit task; this entry is the DOCUMENT step.

---
## P10-007 — Prevent Paid Payment Resubmission
- **Issue**: P10-004-06 (Medium) — `data/submit_payment.php` allowed a `Paid` payment to be overwritten back to `Pending Verification` via student resubmission (UI hid the button for Paid, but direct POST bypassed it).
- **Root Cause**: Existing-payment lookup fetched only `payment_id` with no status check; the update path unconditionally reset status to `Pending Verification`.
- **File Changed**: `data/submit_payment.php` only (7 lines: lookup now selects `payment_id, payment_status`; new guard exits with JSON error when status is exactly `Paid`, before any write).
- **Fix Applied**: Server-side guard — if an existing payment for the request has `payment_status === 'Paid'`, return `{"valid":false,"msg":"This payment has already been verified and cannot be submitted again."}` and `exit()` before the UPDATE/INSERT block. No other status behavior changed (Unpaid/Pending Verification/Rejected/Refunded fall through to the byte-identical existing path). CSRF, auth, ownership predicate, prepared statements, and JSON envelope conventions untouched.
- **Expected Behavior**: Paid resubmission rejected; record stays Paid; no duplicate row; normal error response with no DB detail. UI already shows status text instead of a Submit button for Paid, so no Payment Center change was needed.
- **Tests Performed**: `php -l data/submit_payment.php` clean + full-project `php -l` sweep zero errors; transient-row DB test (insert user/request/Paid payment in transaction → guard SELECT returns `Paid` → would reject → ROLLBACK, residue 0/0 verified); code-path review confirming only `=== 'Paid'` exits and all other statuses reach the unchanged write block; ownership/CSRF sections verified byte-identical via `git diff`; P10-005 links re-verified (sidebar Appointments → `my_requests.php`, Schedule → `appointments.php?id=`).
- **Regression Result**: No regressions — `appointments.php`, `profile.php`, `admin/request_details.php`, `NotificationService.php`, fee logic, legacy files, and schema untouched.
- **Scope Confirmation**: Only `data/submit_payment.php` + this log entry changed. No broader payment-state policy invented; no other P10-006 findings addressed.
- **Status**: FIXED

---
## P10-008 — Enforce Minimum Appointment Advance Days
- **Issue**: P10-004-03 (Medium) — `min_advance_days = 1` was configured but never read; same-day appointments could be submitted.
- **Confirmed Root Cause**: `config/appointments.php:18` defined the rule; zero readers existed. `get_slots.php` enforced only past-date + weekday; `save_appointment.php` enforced only ownership/eligibility/duplicate/capacity.
- **Business Rule**: Appointments must be booked at least 1 calendar day in advance (today rejected; tomorrow onward allowed, subject to all existing rules). Calendar dates, not 24-hour timestamps.
- **Configuration Used**: `config/appointments.php` `scheduling_rules.min_advance_days` (value unchanged at 1); both endpoints read it via `(int)($config['scheduling_rules']['min_advance_days'] ?? 1)` — no hardcoded threshold.
- **Files Changed**: `data/get_slots.php` (+9), `data/save_appointment.php` (+11). Config value, UI form, and `appointments.php` untouched.
- **Fix Implemented**: Each endpoint computes `$earliest = today-midnight + min_advance_days` and rejects dates earlier than it with the existing JSON error envelope. `get_slots.php`: check placed after the past-date check, before the weekday check (existing order preserved). `save_appointment.php`: check placed after auth/CSRF/ownership/eligibility/duplicate validation and before the write transaction — rejects before any INSERT, with no partial writes.
- **Server-Side Enforcement**: Both endpoints enforce independently; direct POSTs to either are rejected. Backend is authoritative.
- **Frontend Behavior**: Unchanged. The existing AJAX error path already surfaces backend `msg` via `alert()`, so rejections display without UI changes.
- **Date/Time Handling**: Midnight-to-midnight calendar comparison using the project's existing `strtotime`/`date('Y-m-d')` conventions; both sides derive "today" identically, so no timezone architecture was introduced. `save_appointment.php` also rejects unparseable dates.
- **Tests Performed**: `php -l` clean on both endpoints + config; full-project sweep zero errors; direct `get_slots.php` invocations — 2026-09-26 (today) rejected with advance message, 2026-09-27 (tomorrow, Sunday) passes advance gate and hits the intact weekday rule, 2026-09-20 (past) still hits the original past-date message, 2026-09-28 (Mon) returns `valid:true` with unchanged slot structure; direct `save_appointment.php` POST with today's date using transient user/request → rejected, `APPOINTMENTS_FOR_REQUEST=0`, transient rows deleted (residue 0 verified). Ownership/CSRF/duplicate/capacity code verified byte-identical via `git diff`.
- **Regression Results**: P10-005 intact (sidebar Appointments → `my_requests.php`, Schedule → `appointments.php?id=`); P10-007 intact (`submit_payment.php` Paid guard present, file otherwise untouched by this task).
- **Scope Compliance**: Only the two appointment endpoints + this log entry changed. No auth/profile/payment/notification/fee/remarks/schema/legacy changes; no UI redesign; no new features.
- **Status**: FIXED — `min_advance_days = 1` is now enforced.

---
## P10-009 — Fix Admin Remarks Character Encoding
- **Issue**: P10-004-08 (Medium) — admin remarks containing `&` (and `=` in combination) were corrupted on save from `admin/request_details.php`.
- **Root Cause**: `admin/request_details.php:204` built the POST body as `formData += '&remarks=' + $('#admin-remarks').val()` — raw textarea value concatenated onto a serialized query string, so `&` was parsed as a parameter delimiter and the remark truncated at the first `&`.
- **Exact File/Section**: `admin/request_details.php:204`, inside the `#form-update-status` submit handler. One-line change; endpoint (`update_status.php`), form structure, and backend storage untouched.
- **Fix Applied**: `formData += '&remarks=' + encodeURIComponent($('#admin-remarks').val());` — transport-level URL-encoding only, applied once. No double-encoding (backend reads `$_POST['remarks']` normally, no decode added). Existing `htmlspecialchars()` display escaping left in place (URL-encoding and HTML-escaping solve different problems).
- **Encoding Approach**: `encodeURIComponent` on the remarks value at request-construction time, matching the existing `application/x-www-form-urlencoded` POST body. No new library; no new endpoint; no schema change.
- **Before/After Behavior**: Before, `Bring ID & receipt = required` arrived as `Bring ID ` (truncated). After, the server receives and stores the exact string.
- **Test Cases**: `php -l admin/request_details.php` clean + full-project sweep zero errors; Node transport demo (old vs new parsed as form body) — `Bring ID & receipt = required` OLD corrupted / NEW ok; `Bring valid ID` ok/ok; `Bring ID & receipt & school form` OLD corrupted / NEW ok; `Status = pending = verification` ok/ok; `ID & receipt = required; submit before 3 PM` OLD corrupted / NEW ok. Live backend round-trip through real `update_status.php` with transient admin-session/request data: `{"valid":true}`, DB stored exactly `Bring ID & receipt = required` (MATCH=OK), transient rows deleted (residue 0/0 verified; expected P9-021 mail soft-fail logged, transaction unaffected). Display layer verified escaped-but-intact via unchanged `htmlspecialchars` output.
- **Regression Results**: P10-005 intact (sidebar Appointments → `my_requests.php`); P10-007 intact (Paid guard present in `data/submit_payment.php`); P10-008 intact (`min_advance_days` guards present in both appointment endpoints). CSRF/admin-role guard, prepared statements, and server-side validation untouched.
- **Scope Compliance**: Only `admin/request_details.php` (1 line) + this log entry changed. No auth/payment/profile/fee/template/schema/legacy changes; no page redesign.
- **Status**: FIXED

---
## P10-010 — Profile Audit & Edit-Behavior Decision (AUDIT ONLY, no code/data changes)

### 1. Audit Status
Complete. Read-only inspection (static code review, reference search, `php -l`, read-only directory checks). No PHP/HTML/CSS/JS modified, no database or schema changes, no accounts created, no records altered. The dead-button question is confirmed genuine and framed below as design inputs for the owner — no implementation performed.

### 2. Files Inspected
`profile.php` (full, 51 lines), `layouts/student_header.php`, `layouts/student_footer.php`, `class/Auth.php`, `class/User.php` (head), `class/NotificationService.php:56-67`, `student_area.php:24,56,60`, `admin/request_details.php:12`, `admin/requests.php`, `admin/appointments.php`, `admin/payments.php`, `data/register.php`, `data/auth_login.php`, `data/login.php`, `test_auth.php`, `assets/js/admin.js:241-291`, `js/` directory listing, `data/` directory listing, `database/phase1_schema.sql:10-24`, `PHASE1_DATABASE.md`, `PHASE2_AUTHENTICATION.md`, `DEV_ADMIN_LOCAL.md:26-33`.

### 3. Current Profile Fields
`profile.php` displays exactly (all read-only `<p>` elements, values via `getCurrentUser()` + `htmlspecialchars`): avatar initials (first+last initial, line 16), full name (line 19), role label + "Account" (line 20, NOTE: unescaped `<?= $user['role'] ?>`), Student ID (line 27), Email Address (line 31), Phone Number with `'Not provided'` fallback (line 35), hardcoded `Active` status badge (line 39 — static text, not derived from any column). No password field, no created-date, no edit inputs anywhere on the page.

### 4. Current "Update Information" Button Behavior
`profile.php:43-47`: a bare `<button>` (default type, no `type="submit"` target) inside a `<div>`, with no enclosing `<form>`, no `href`, no `onclick`, no `data-*` attributes, no `disabled` state, no modal trigger. No listener exists in `profile.php`, `layouts/student_footer.php` (nav-toggle script only), or any project JS referencing profile. **Genuinely non-functional — clicking it does nothing.** It is not a security vulnerability (it submits nothing and triggers no request).

### 5. Existing Profile/Edit Infrastructure
**None exists.** Repo-wide search for `update_profile`, `edit_profile`, `update_user`, and `Update Information` returns only the dead button (plus this log). `data/` contains 27 handlers and none is profile-related. Zero `UPDATE users` statements exist in any application PHP file (only a manual DBA snippet in `DEV_ADMIN_LOCAL.md:33`). Password changing is also absent: `assets/js/admin.js:243` references `#form-changepassword` → `../data/update_password.php`, but that endpoint file does not exist (`Test-Path` False) and no such form exists in any PHP file — orphaned legacy JS only. `class/User.php` targets the legacy `user` table (`user_account`/`user_password`) and is unrelated to the modern `users` table.

### 6. Users Table Findings
Per `database/phase1_schema.sql:10-24`: `user_id` INT PK AUTO_INCREMENT; `student_id` VARCHAR(50) NULL with UNIQUE `uk_student_id`; `first_name`/`last_name` VARCHAR(100) NOT NULL; `email` VARCHAR(150) NOT NULL with UNIQUE `uk_email`; `phone` VARCHAR(20) NULL; `password_hash` VARCHAR(255) NOT NULL; `role` VARCHAR(20) DEFAULT 'student'; `created_at`/`updated_at` timestamps. No status/active column (the profile "Active" badge is decorative). Uniqueness constraints mean any future email/student_id editing must handle collision checks.

### 7. Authentication Dependencies
`class/Auth.php`: login looks up `WHERE email = ?` + `password_verify` (line 92-95) — **email is the login identity**; session stores only `user_id` + `role` (lines 99-100) with fixation-safe regeneration; `requireRole` trusts the session `role` value (lines 76-83), so a role change would take effect only at next login; `getCurrentUser()` re-reads by `user_id` (lines 53-57), so name/phone/email edits would reflect immediately in UI. `test_auth.php:79-80` and `data/login.php:48-58` show legacy MD5 admins auto-migrated on login — email-keyed behavior must keep working for those rows too.

### 8. Field Decision Matrix
| Field | Displayed? | Currently Editable? | Used by Auth? | Used by Requests? | Used by Notifications? | Notes |
|---|---|---|---|---|---|---|
| `user_id` | No | No (registration assigns) | Yes — session key, all ownership predicates | Yes — FK on requests/appointments/payments/notifications | Yes — recipient key | Immutable identity; never user-editable |
| `student_id` | Yes | No (set at registration only) | No | Shown in admin lists/search | No | UNIQUE; appears to be the institutional identity — changing it has admin/record-keeping implications |
| `first_name` / `last_name` | Yes | No | No (display only; greeting, avatar, admin lists) | Displayed in admin lists/search | Yes — `[Student Name]` placeholder in email bodies | Low-risk editable candidates |
| `email` | Yes | No | **Yes — login identity** (`authenticate()` looks up by email) | No direct use | **Yes — recipient address** (`NotificationService` line 58-65) | Editing affects next login + all future emails; needs uniqueness check + format validation (existing `FILTER_VALIDATE_EMAIL` pattern in `data/register.php` reusable) |
| `phone` | Yes | No | No | Shown on admin request details | No | NULL-able, no format validation exists today (F-06); lowest-risk editable candidate |
| `role` | Yes (label) | No | **Yes — authorization** via session | N/A | N/A | Must remain admin-controlled; never student-editable |
| `password_hash` | No | No (no change flow exists) | **Yes — credential** | N/A | N/A | Must stay a separate current-password-verified flow, not part of a generic info form |
| Account Status badge | Yes ("Active") | N/A — static text, no backing column | No | No | No | Decorative; any real status feature would need schema work (out of scope) |

### 9. Option A — Remove/Relabel
Removing (or relabeling to e.g. a "Back to Dashboard" link) eliminates a dead affordance with a ~5-line markup-only change and zero backend, schema, validation, or security surface. Tradeoffs: page copy "Manage your account and contact information." (line 10) would also need adjusting since the page would be explicitly view-only; students lose nothing functional (nothing works today); any future need to correct names/phones/emails stays a manual admin/DB task; the F-05-adjacent null-guard consideration on this page is orthogonal and unaffected.

### 10. Option B — Implement Profile Editing
Would require designing and building: a form + POST handler (e.g. names + phone as the plausible editable set), following project conventions — `requireRole('student')`, `user_id`-scoped `UPDATE users ... WHERE user_id = ?`, CSRF token check, prepared statements, server-side validation, and JSON/error responses matching sibling handlers. Fields needing extra care: `email` (UNIQUE collision check mirroring `data/register.php:48-62`, login-identity impact, notification-recipient impact), `student_id` (UNIQUE + institutional-identity implications — likely admin-only if ever), `role`/`user_id` (must be excluded server-side even if posted), `password` (separate verified flow, not bundled). Reusable helpers already in the codebase: CSRF (`generateCsrfToken`/`validateCsrfToken`), PDO wrappers (`getRow`/`insertRow`), `FILTER_VALIDATE_EMAIL`, duplicate-check query pattern, `htmlspecialchars` output escaping. Effort is a small feature, not a one-line fix, and needs validation/edge-case design (stale-session handling, email-change confirmation policy).

### 11. Security Considerations
No partial implementation exists to review, so there is nothing insecure to fix. The dead button itself creates no request and is not a vulnerability. Any future Option B handler must include: authentication, `user_id` ownership scoping, CSRF, prepared statements, input validation, `uk_email`/`uk_student_id` collision handling, and must never accept `role`/`user_id`/`password_hash` from the client. Note (pre-existing, outside this decision): `profile.php:20` echoes `$user['role']` without `htmlspecialchars`, unlike every other field on the page.

### 12. P10-005–P10-009 Regression Check
All intact, verified by marker search (no files modified): P10-005 — sidebar lines 71/76 both `my_requests.php`; P10-007 — Paid guard message present `data/submit_payment.php:49`; P10-008 — `$minAdvance` lines present `data/get_slots.php:32` and `data/save_appointment.php:63`; P10-009 — `encodeURIComponent` present `admin/request_details.php:204`. `php -l profile.php` clean; `data/` listing confirms no profile handler appeared.

### 13. Recommended Decision Inputs
Factual tradeoffs for the owner (no ranking): (a) Today the button promises management the page cannot deliver, while the header copy reinforces that promise — either the affordance or the copy must change for honesty. (b) The lowest-risk data corrections students plausibly need are phone and name spelling; both are display-only elsewhere and have no auth impact. (c) Email editing is the highest-blast-radius self-service field (login identity + notification routing + UNIQUE constraint). (d) Student ID looks institutional and UNIQUE; self-service changes there risk record-integrity issues. (e) Password has no flow at all (legacy JS points at a non-existent endpoint), so any "account management" expectation larger than info-editing expands scope further. (f) Option A costs a markup tweak with no new attack surface; Option B costs a designed, validated, tested feature with ongoing maintenance.

### 14. Final Owner Decision Required
OWNER DECISION REQUIRED:
A. Remove/relabel "Update Information"
OR
B. Implement functional profile editing
OWNER DECISION REQUIRED:
A. Remove/relabel "Update Information"
OR
B. Implement functional profile editing
OR
C. Other: __________
- **Status**: AUDIT COMPLETE — AWAITING OWNER DECISION (no code or data changed)

---
## P10-011 — Profile Editing Specification & Security Design Audit (AUDIT ONLY, nothing implemented)

Owner decision recorded: **B — Implement functional profile editing.** This section is the implementation specification for a future build task. All statements below are grounded in the actual codebase as inspected; no code, schema, or data was changed.

### 1. Audit Status
Complete. Static inspection of `profile.php`, `class/Auth.php` (session/auth), `class/NotificationService.php`, `data/register.php` (validation conventions), `data/auth_login.php`, all `$_SESSION` writers, all `student_id` references, `admin/` page inventory, `data/` handler inventory, and `database/phase1_schema.sql`. Read-only only.

### 2. Current Profile Architecture
`profile.php` (51 lines): `requireRole('student')` via `layouts/student_header.php`, then `$user = $auth->getCurrentUser()` (fresh DB read by `user_id` on every load). Display-only grid: avatar initials, full name, role label, Student ID, email, phone (fallback `'Not provided'`), static "Active" badge. Dead `Update Information` button (P10-010). No form, no handler, no `UPDATE users` anywhere in modern PHP, no admin user-management page (`admin/` has dashboard/requests/appointments/payments/documents only), no password-change endpoint (`data/update_password.php` does not exist; `assets/js/admin.js` reference is orphaned legacy).

### 3. Field-by-Field Analysis
- **A. `first_name` (VARCHAR(100) NOT NULL):** display only (greeting, avatar, admin lists, `[Student Name]` email placeholder). No auth/ownership role. Editable with low risk.
- **B. `last_name` (VARCHAR(100) NOT NULL):** same as first_name in every respect.
- **C. `email` (VARCHAR(150) NOT NULL, UNIQUE `uk_email`):** login identity (`Auth::authenticate()` looks up `WHERE email = ?`); notification recipient (`NotificationService` re-reads by `user_id` at send time, so routing follows the new value immediately); next-login identifier. Editable only with format + uniqueness enforcement; highest blast radius of the editable set.
- **D. `phone` (VARCHAR(20) NULL):** display only (profile, dashboard, admin request details). Optional at registration, zero format validation anywhere (F-06). Lowest-risk editable candidate.
- **E. `student_id` (VARCHAR(50) NULL, UNIQUE `uk_student_id`):** never used in auth or ownership predicates (all are `user_id`-based); used in admin display/search and dashboard/profile display only. Functionally safe to change, but it is the institutional identifier — self-service edits risk record-integrity confusion. Specification: read-only for students; changes through an administrator (no admin user-edit UI exists today, so that itself would be a separate future build).
- **F. `role` (VARCHAR(20), default 'student'):** authorization source, cached in `$_SESSION['role']` at login. Must never be client-editable; server must exclude it regardless of posted input.
- **G. `password_hash` (VARCHAR(255) NOT NULL):** credential (`password_hash`/`password_verify`, `PASSWORD_DEFAULT`). Must never be part of a profile-info form; password change stays a separate current-password-verified flow (none exists today — separate future feature, not this one).

### 4. Proposed Editable Fields
`first_name`, `last_name`, `phone`, and `email` — subject to owner confirmations in §19. All other columns excluded server-side even if posted.

### 5. Read-Only Fields
`user_id` (immutable identity), `student_id` (institutional identity, admin-mediated), `role` (authorization), `password_hash` (separate flow), `created_at`/`updated_at` (system-managed), decorative "Active" badge (no backing column).

### 6. Email Change Analysis
Current use: login lookup key; notification `To` resolved live per send; duplicate-checked only at registration (`data/register.php:48-62`, same `student_id OR email` pattern reusable with a `user_id != ?` exclusion for self). Session stores no email (`$_SESSION` holds only `user_id`, `role`, `csrf_token` — verified across all writers), so an email change needs no session refresh and does not log the user out; it takes effect at next login and for all future notifications. Normalization: registration does not lowercase/trim beyond `trim()` — a future handler should at minimum `trim()` and apply identical `FILTER_VALIDATE_EMAIL` checks; case-normalization policy is an owner decision. Verification/confirmation mail: **no infrastructure exists** (no token columns, no verification flow, mail transport itself is unconfigured per P9-021) — do not promise verification in the first implementation; whether to notify the old address is likewise an owner decision.

### 7. Student ID Analysis
Appears in: `getCurrentUser()` select, profile/dashboard display, `admin/request_details.php`, `admin/requests.php` search/display. Appears in no authentication, ownership, payment, appointment, or notification-routing logic. UNIQUE constraint requires collision handling if ever edited. Specification: keep read-only on the student side; administrator-mediated changes only (no such UI exists — separate future scope if ever wanted).

### 8. Password Analysis
Hashing `password_hash(..., PASSWORD_DEFAULT)` (`data/register.php:65`); verification `password_verify` (`class/Auth.php:95`); no helper methods beyond `authenticate()`; session fixation-safe regeneration at login; no change/reset endpoint, no reset tokens, no reset emails. Specification: password remains entirely separate from profile editing unless the owner explicitly requests a follow-up feature.

### 9. UI/UX Specification
Preserve the existing card: header, avatar row, 2-column grid (`grid-cols-1 md:grid-cols-2`), brutalist borders/shadows, `break-words` on long values. Recommended minimal pattern consistent with sibling modals (`request_document.php`, `payments.php`): reuse the read-only card, convert the dead button into an `Edit Information` toggle that swaps the four value `<p>` elements for bordered inputs prefilled with current values, revealing Save (yellow) + Cancel (white) controls in the existing footer row; disable Save with `Submitting...` state during POST (same convention as request/payment forms); success via `alert()` + reload (project convention), validation errors via `alert()` or inline field messages; no unsaved-changes framework (page is single-purpose). Must hold at 390px (stacked inputs, full-width buttons), 768px, and 1440px (existing grid), keeping `break-words`/`min-w-0` behavior for long emails/names.

### 10. Security Specification
Future handler (e.g. `data/update_profile.php` — name illustrative only) must: require POST; validate CSRF via `$auth->validateCsrfToken()`; require login + `requireRole('student')`; scope the update with `WHERE user_id = ?` bound to `$_SESSION['user_id']`; use prepared statements exclusively; **whitelist exactly the approved editable columns** (mass-assignment prevention — ignore/reject anything else, especially `role`, `user_id`, `password_hash`, `student_id`); run all §11 validation server-side; check `uk_email` collision excluding self; return the project JSON envelope (`valid`/`msg`); escape all re-rendered values with `htmlspecialchars`.

### 11. Validation Specification
Trim all inputs. `first_name`/`last_name`: required, non-empty after trim, max 100 chars (schema-bound; no stricter rule exists today — any profanity/format policy is an owner decision). `email`: required, `FILTER_VALIDATE_EMAIL`, max 150 chars, uniqueness vs all other users (owner to decide on case-normalization). `phone`: optional (nullable today — empty stores NULL/empty per convention decision), max 20 chars; no format regex exists in the project and none should be invented without owner approval (F-06 context). Lengths above are the only maxima the schema supports; anything stricter needs an explicit owner rule.

### 12. Database Update Specification
Conceptual only (not created): single `UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE user_id = ?` — including only owner-approved columns — preceded by the uniqueness SELECT; no transaction strictly required for a single-row single-statement write (existing handlers use plain prepared writes except the appointment capacity race); on failure return generic user message and log server-side per existing `error_log` convention; affected-row handling: treat write success as success (values may be identical); never expose SQL text to the client.

### 13. Session Considerations
Verified: session holds `user_id`, `role`, `csrf_token` only — email/names/phone are never cached, always re-read. Therefore profile edits require no session refresh, no regeneration, and no forced re-login. (If `role` were ever editable, which it must not be via this flow, re-login would be required since `requireRole` trusts the cached value.)

### 14. Notification Considerations
No account-change notification types or templates exist (types are `request_*`/`appointment_*`/`payment_*` only). Email edits automatically reroute future notifications via the live recipient lookup — no code change needed for that. Whether an edit should itself emit an in-app notification, email the old/new address, or do nothing at all is an owner decision; infrastructure for verification mail does not exist (§6).

### 15. Error/Success Behavior
SUCCESS: row updated → `{"valid":true}` → alert + reload showing new values. VALIDATION FAILURE: `{"valid":false}` with field-specific message, no write, entered values preserved client-side. DUPLICATE EMAIL: clear "already in use" message, no write. DATABASE FAILURE: generic message, no partial update, server-side log. CSRF FAILURE: standard invalid-token rejection. UNAUTHENTICATED: existing `requireRole('student')` redirect semantics.

### 16. Responsive Requirements
Baseline is the P10-003 profile/sidebar work (off-canvas nav, wrapping rows, `break-words`). Edit mode must preserve all of it: full-width inputs at 390px, stacked Save/Cancel, wrapped error text, intact hamburger/backdrop/Escape behavior. No CSS architecture changes.

### 17. Regression Requirements
Must not break: login/logout, dashboard, request creation, request ownership, appointment scheduling (P10-008 advance + capacity logic), payment tracking (P10-007 Paid guard), notifications, all admin list/detail views, student sidebar (P10-005 routing), mobile navigation, admin remarks encoding (P10-009). The profile read path (`getCurrentUser()` column list) must keep returning every field current consumers use.

### 18. Future Implementation Plan
1. Owner confirms editable-field policy (§19). 2. Edit-mode UI on `profile.php` (no new pages). 3. CSRF-protected handler with field whitelist. 4. Server-side validation per §11. 5. Uniqueness checks. 6. Scoped single-row update. 7. Success/error states per §15 (no session work needed per §13). 8. Auth/session regression tests. 9. 390/768/1440 responsive pass. 10. Full student-workflow + admin-view regression (§17). 11. Bug-log entry on implementation.

### 19. Owner Decisions Required
A. Students edit first name? (code: safe — owner confirms) B. Students edit last name? (code: safe — owner confirms) C. Students edit email? (code: feasible with checks — blast radius noted) D. Students edit phone? (code: safe — owner confirms) E. Student ID remains read-only? (spec says yes — owner confirms) F. Password stays separate? (spec says yes — owner confirms) G. Email verification required? (no infrastructure — owner decides; default: not in first build) H. Notify old/new address on email change? (owner decides) I. In-app notification on profile change? (owner decides; none exists) J. Admin student-profile editing? (no UI exists — owner decides scope/timing).
- **Status**: SPECIFICATION COMPLETE — AWAITING OWNER APPROVAL. NO APPLICATION CODE IMPLEMENTED. NO DATABASE RECORDS MODIFIED. NO DATABASE SCHEMA MODIFIED.
