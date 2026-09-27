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
- **Update (P10-016)**: FIXED — the three dedicated templates were added (`class/NotificationService.php`) plus the minimum `remarks` addition to payment `$emailData` (`admin/update_payment.php`). See P10-016 entry below. Status now FIXED.

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

---
## P10-012 — Student Profile Editing Implementation
- **Files Changed**: `profile.php` (read-only card → `form#form-profile` with prefilled inputs + inline message box + Save/Cancel; layout, brutalist styling, grid, avatar row, read-only Student ID/Status untouched).
- **Files Created**: `data/update_profile.php` (dedicated POST handler, project conventions: POST-only, CSRF, `{"valid","msg"}` JSON envelope, PDO prepared statements via `$auth` helpers).
- **Editable Fields**: `first_name`, `last_name`, `email`, `phone` — explicit fixed-column SQL, no dynamic column construction (mass-assignment prevention).
- **Read-Only Fields**: `user_id` (session-only, never from client), `student_id`, `role`, `password_hash`, timestamps — never referenced in the UPDATE; verified immune to posted overrides.
- **Validation**: trim all; names required ≤100; email required ≤150 + `FILTER_VALIDATE_EMAIL`; phone optional ≤20, empty stored as NULL (nullable column); no invented regexes.
- **Email Uniqueness**: `WHERE email = ? AND user_id <> ?` pre-check with clear error and no write, plus 1062/duplicate fallback in catch without leaking SQL.
- **Email Behavior**: DB holds new value; future login + notifications follow it (live lookups); session untouched (caches no email) so no logout — verified `LOGIN_NEW_EMAIL=OK` with pre-change password.
- **CSRF/Auth/Ownership**: CSRF token required/validated; login + `role === 'student'` enforced with JSON rejection; update scoped `WHERE user_id = ?` from session only.
- **Database Update**: single `UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE user_id=?`; no schema change; no new tables/columns.
- **UI Behavior**: Save posts via AJAX with `Saving...` state; inline green/red message box (no alert dependency); success refreshes header name + avatar initials live; Cancel is a plain `profile.php` reload link (no write). Read-only Student ID/Status preserved; responsive grid and sidebar intact.
- **Testing Performed**: `php -l` clean on both files + full sweep zero errors; inline JS passes `node --check`; live round-trip with two disposable students through the real handler — success update persisted, duplicate/invalid/blank/long-phone rejected with exact messages, empty phone stored NULL, mass-assignment POST (`role=admin`, `student_id=HACKED`, `password_hash`, foreign `user_id`) left role/student_id/peer untouched, bad-CSRF/unauthenticated/wrong-method rejected; both disposables deleted afterward (residue 0 verified). Browser click-through at 390/768/1440 not available here — recommended on owner side.
- **Regression Checks**: P10-005 (sidebar → `my_requests.php`), P10-007 (Paid guard), P10-008 (`$minAdvance` in both endpoints), P10-009 (`encodeURIComponent`) all present; no other workflow files touched.
- **Limitations**: No email verification, no account-change notifications, no password flow, no admin editing (all per approval). Pre-existing `profile.php:20` unescaped role echo left untouched.
- **Status**: FIXED

---
## P10-013 — Same-Browser Student/Admin Session & Public Home Audit (AUDIT ONLY, nothing implemented)
- **Classification**: Expected same-browser session behavior + role-routing design consequence. NOT a confirmed vulnerability; NOT a session bug. Awaiting owner decision on the Public-Home-vs-Application-Root design only.
- **1. Observed Behavior**: Student logs in (tab 1); admin area opened in same browser (tab 2); "Return Public Home" clicked; student dashboard appears. Reproduced by static trace (live browser session testing unavailable; no accounts created, no data written).
- **2. Root Route Analysis** (`index.php:5-21`): A. no session → renders public landing (200, verified). B. student session → `302 student_area.php` (DB role re-read via `getCurrentUser()`, not session cache). C. admin session → `302 admin/dashboard.php`. D. stale session (row deleted) → logout → `login.php`. Unknown role strings fall to the `else` → student dashboard (role allowlist gap noted, not exploited — roles only ever come from the DB `role` column).
- **3. Return Public Home Analysis**: The ONLY such link is `admin/index.php:59` (`← Return to Public Home` → `../index.php`) on the admin LOGIN page, which renders unguarded (HTTP 200 verified). The authenticated admin sidebar (`layouts/admin_header.php:60-93`) contains NO home link — only Dashboard/Requests/Appointments/Payments/Documents + Logout. So the reported click path necessarily passes through the login page, not the admin dashboard.
- **4. Student Login Session Analysis** (`data/auth_login.php` → `Auth::authenticate()`): writes `$_SESSION['user_id']` + `$_SESSION['role']` with `session_regenerate_id(true)`; session holds only `user_id`, `role`, `csrf_token` (verified across every `$_SESSION` writer — email/name never cached).
- **5. Admin Login Session Analysis** (`data/login.php` → same `Auth::authenticate()`): same session, same keys, same regeneration; then enforces admin-only — non-admin gets `Access denied` JSON (note: `authenticate()` has already written that user's session at that point, so a student rejected by the admin form remains logged in as student). Legacy MD5 path migrates the account then writes `role=admin`. No `session_name()` anywhere → default `PHPSESSID`; no separate namespaces/cookies.
- **6. Session/Cookie Architecture**: Single shared PHP session for the whole application. Browser tabs always share cookies/session by design — there is no per-tab context in this (or any standard PHP) architecture.
- **7. Same-Browser Behavior**: Reconstructed exactly: student session active → tab 2 hits any `admin/*` content page → `requireRole('admin','index.php')` → 302 to `admin/index.php?error=unauthorized` → login form renders (200) → "Return to Public Home" → `index.php` → sees the still-student session → 302 `student_area.php`. Every step is correct per code; no session anomaly is required to explain the observation. Had an actual admin login occurred in tab 2, the SAME session would now read role=admin and Public Home would land on `admin/dashboard.php` instead — i.e., the observed student-dashboard outcome itself proves the session was still student-role.
- **8. Authorization Analysis**: Admin content pages enforce `requireRole('admin','index.php')` (`layouts/admin_header.php:4`, `admin/session_login.php:6`); student pages enforce `requireRole('student')` (`layouts/student_header.php:4`); all student data queries scope `WHERE user_id = $_SESSION['user_id']`; all write handlers re-check ownership + CSRF. Live read-only checks: logged-out `admin/dashboard.php` → 302, `admin/requests.php` → 302, `student_area.php` → 302 (nothing renders unauthenticated).
- **9. Security Impact**: A. redirect working as coded. B. dashboard shown matches the genuine session role. C–E. No unauthorized access path found — a student session cannot render any admin content page (guard redirects before output), and vice versa. F–G. No fixation (regeneration on every login; cookie cleared + `session_destroy()` on logout) and no escalation (role comes from DB row, never client input). H. No leakage identified. I. The finding is (I): expected behavior, not a vulnerability. Residual note (not a vulnerability): a student rejected by the admin login form keeps their student session (§5) — correct fail-closed outcome, just worth knowing.
- **10. Simultaneous Student/Admin Session Support**: NOT supported by design — one `user_id`/`role` pair per browser profile. There is no multi-context mechanism, and none is needed unless the owner explicitly requires concurrent student+admin use (would need separate browser profiles today, or a new auth architecture).
- **11. Public Home vs Application Root Analysis**: Confirmed conflated — `index.php` is BOTH the public landing (logged-out) AND the authenticated root router (logged-in). Any in-app "Public Home" link pointing at it can never show the landing page to a logged-in user; it role-redirects by design. The label "Return to Public Home" on the login page therefore overpromises for authenticated visitors.
- **12. Potential Remediation Approaches** (documented only, no ranking): A. separate always-public landing route that never role-redirects; B. retarget in-app home links to it; C. keep single session and document that student/admin cannot be simultaneously active per browser profile; D. only if concurrent use becomes a requirement — separate auth contexts (new architecture, explicitly out of current scope).
- **13. Regression Check**: P10-005 (sidebar lines 71/76 → `my_requests.php`), P10-007 (Paid guard line 49), P10-008 (`$minAdvance` both endpoints), P10-009 (`encodeURIComponent` line 204), P10-012 (`form#form-profile` + handler markers) all present. No files modified.
- **14. Testing Evidence**: Static traces of all listed flows; live read-only HTTP (logged-out 302s above, 200s for `/`, `admin/index.php`); `php -l` unaffected (no code touched). No live same-browser session reproduction available — stated explicitly; conclusion rests on code evidence, which is conclusive for the routing question.
- **15. Owner Decision Required**: A. accept current behavior as correct and optionally relabel/retarget the login-page home link; OR B. approve a dedicated public-landing route split; OR C. Other: __________. No authorization changes needed under any option.
- **Status**: AUDIT COMPLETE — AWAITING OWNER DECISION. NO APPLICATION CODE MODIFIED. NO DATABASE SCHEMA MODIFIED. NO DATABASE RECORDS MODIFIED.

---
## P10-014 — Split Public Landing Route from Authenticated Root
- **Finding**: P10-013 showed `index.php` served both the public landing (logged-out) and the authenticated root router, so "Return to Public Home" could never show the landing page to a logged-in user. **Decision**: owner selected Option B — split the routes.
- **Implementation**: Created `public_home.php` (landing markup verified byte-identical to the former `index.php` landing block; zero session/auth logic — always renders). `index.php` is now a pure router: logged-out → `login.php`, student → `student_area.php`, admin → `admin/dashboard.php` (deleted-user guard preserved; Auth/CSRF/sessions untouched). Retargeted public-landing-intent links: `admin/index.php` "Return to Public Home" → `../public_home.php`; `login.php`/`register.php` "Return to Home" → `public_home.php` (required — otherwise they loop into `login.php`). Kept as authenticated-root: `logout.php`/`admin/logout.php` targets and all `requireRole` redirects. Legacy travel files (`reserved.php`, `payment.php`, `passenger.php`, `accomodation.php`, `admin/transaction.php`) left untouched per scope — note their "Back To Home" navbar links now resolve to the login redirect rather than a landing page; documented, not changed.
- **Testing** (all actually performed): `php -l` clean on all 5 files + full-project sweep zero errors. Live HTTP with real sessions (2 disposable accounts, deleted after, residue 0): TEST1 logged-out `public_home.php` → 200 landing; TEST2 logged-out `index.php` → 302 `login.php`; TEST3 student `index.php` → 302 `student_area.php`; TEST4 admin `index.php` → 302 `admin/dashboard.php`; TEST5 student `public_home.php` → 200 landing; TEST6 admin `public_home.php` → 200 landing; TEST7 student `admin/dashboard.php` → 302 `admin/index.php?error=unauthorized`; TEST8 logged-out protected pages → 302. Same-browser scenario: student session → admin login page (200, corrected link) → `public_home.php` (200) → `student_area.php` still 200 (session intact, no logout).
- **Security Checks**: `requireRole`, ownership, CSRF, password/session handling untouched; denial behavior explicitly re-verified live (TEST7/8). No new attack surface (`public_home.php` is static markup, no inputs, no queries).
- **Regression**: P10-005/007/008/009/012 markers all present; no business-logic files touched.
- **Remaining**: legacy "Back To Home" links documented above (owner decision if ever wanted); no new owner decisions required by this task.
- **Status**: FIXED

---
## P10-015 — Notification Email Template Audit (AUDIT ONLY, no code/data changes)

### 1. Scope
Verify whether the three email-template gaps reported in P10-004-07 (`request_cancelled`, `appointment_cancelled`, `payment_rejected` falling back to a generic email) are still present in the current code, and whether each notification type is actually reachable through current SPVAI workflows. Audit only — no application code modified, no templates added, no triggers/statuses/UI/auth changed.

### 2. Files Inspected
`class/NotificationService.php` (full, 131 lines — `notifyUser`, `createInAppNotification`, `sendEmailNotification`, `getTemplate`), `config/notifications.php` (SMTP/from config only, no templates), `admin/update_status.php` (full, 104 lines), `admin/update_appointment.php` (full, 85 lines), `admin/update_payment.php` (full, 88 lines), `notifications.php` (in-app list rendering), `data/mark_read.php` (ownership-checked read handler), `data/submit_payment.php` (student payment submit, no notifications emitted), `admin/request_details.php:163-194` (status dropdown), `admin/appointments.php:138-150` (status dropdown), `admin/payments.php:160-172` (status dropdown). Repo-wide searches for `NotificationService|notifyUser|getTemplate`, all `*_cancelled`/`payment_rejected`/`payment_paid` keys, all `UPDATE requests|appointments|payments SET` writers, and all `cancel|Cancel` references. `php -l` on all five notification files (clean). `git log` on the four notification files (untouched since Phase 09).

### 3. Current Notification Type Inventory
Only three callers of `NotificationService` exist in the entire project: `admin/update_status.php:95-96`, `admin/update_appointment.php:76-77`, `admin/update_payment.php:79-80`. All follow the same pattern: build `$type` + `$emailKey` via a status `switch`, fetch `$template = getTemplate($emailKey, $emailData)`, then `notifyUser($details['user_id'], <request_id>, $type, $msg, $template)`. No student-side file emits notifications (`data/submit_payment.php`, `data/save_appointment.php` send none). Template selection is a plain array lookup with a generic fallback (`NotificationService.php:127`): `['subject' => 'SPVAI Notification', 'body' => 'Message regarding your request.']` — no exception, no log, silent downgrade.

| Notification (in-app `type` / email key) | Trigger | Reachable? | In-App | Email | Dedicated Template | Fallback |
|---|---|---|---|---|---|---|
| `request_approved` | `admin/update_status.php:69` (status `Approved`) | Yes — admin dropdown `request_details.php:174` + whitelist `update_status.php:36` | Yes | Yes | A. Dedicated (`SPVAI Document Request Approved`) | — |
| `request_rejected` | `admin/update_status.php:70` (status `Rejected`, remarks required) | Yes — dropdown `:175` + whitelist | Yes | Yes | A. Dedicated (`Rejected`, includes remarks) | — |
| `request_processing` | `admin/update_status.php:71` | Yes — dropdown `:176` + whitelist | Yes | Yes | A. Dedicated | — |
| `request_ready` | `admin/update_status.php:72` (adds appointment info when present) | Yes — dropdown `:177` + whitelist | Yes | Yes | A. Dedicated (optional `app` line) | — |
| `request_completed` | `admin/update_status.php:73` | Yes — dropdown `:178` + whitelist | Yes | Yes | A. Dedicated | — |
| `request_cancelled` | `admin/update_status.php:74` (status `Cancelled`) | Yes — dropdown `:179` + whitelist `:36` includes `Cancelled`; no student-side cancel path exists (cancel is admin-only, but reachable) | Yes | Yes (generic) | MISSING | B. Generic fallback (`SPVAI Notification` / `Message regarding your request.`) |
| `appointment_confirmed` | `admin/update_appointment.php:64` (status `Confirmed`) | Yes — admin dropdown `appointments.php:146` + whitelist `:36` | Yes | Yes | A. Dedicated (includes date/time) | — |
| `appointment_cancelled` | `admin/update_appointment.php:65` (status `Cancelled`) | Yes — dropdown `appointments.php:148` + whitelist includes `Cancelled`; student cannot cancel (no student cancel endpoint; `appointments.php`/`save_appointment.php` only create) | Yes | Yes (generic) | MISSING | B. Generic fallback |
| (`Scheduled`/`Completed`/`No Show` appointments) | `admin/update_appointment.php:63-66` switch | Intentionally silent — switch maps only `Confirmed`/`Cancelled`, `$type` stays empty, no notification at all | No (by design) | No | C. No email sent (no trigger) | — |
| `payment_paid` (in-app) / `payment_verified` (email key alias) | `admin/update_payment.php:68` (status `Paid`) | Yes — admin dropdown `payments.php:169` + whitelist `:36` | Yes | Yes | A. Dedicated via alias (email key `payment_verified` exists; only the in-app `type` string differs) | — |
| `payment_rejected` | `admin/update_payment.php:69` (status `Rejected`) | Yes — dropdown `payments.php:170` + whitelist includes `Rejected` | Yes | Yes (generic) | MISSING | B. Generic fallback |
| (`Unpaid`/`Pending Verification`/`Refunded` payments) | `admin/update_payment.php:67-70` switch | Intentionally silent — no mapping, no notification | No (by design) | No | C. No email sent (no trigger) | — |
| (`Pending` request status) | `admin/update_status.php:68-75` switch | Intentionally silent — no mapping, no notification | No (by design) | No | C. No email sent (no trigger) | — |

### 4. Template Inventory
`getTemplate()` (`NotificationService.php:95-128`) defines exactly 7 dedicated templates: `request_approved`, `request_rejected`, `request_processing`, `request_ready`, `request_completed`, `appointment_confirmed`, `payment_verified`. Emitted email keys total 10 distinct strings across the three callers; of those, `request_cancelled`, `appointment_cancelled`, `payment_rejected` miss the map and receive the generic fallback subject `SPVAI Notification` with body `Message regarding your request.` (note: the body contains no `[Student Name]` placeholder, so no name substitution occurs for these three; all other templates substitute the recipient's `first_name`). `payment_paid` is safe solely because its email key aliases to the existing `payment_verified` template. `config/notifications.php` holds only SMTP/from-address settings — no template content lives there.

### 5. Previous Finding Comparison (P10-004-07)
- **`request_cancelled`**: Still missing. No template added; `update_status.php:74` still emits `$emailKey = 'request_cancelled'`; whitelist (`:36`) and admin dropdown (`request_details.php:179`) still include `Cancelled`, so the trigger is reachable (admin-only; no student cancel path exists anywhere — verified by zero student-side `UPDATE requests` writers). Falls back to generic email; in-app notification still created. No behavior change since the earlier audit.
- **`appointment_cancelled`**: Still missing. No template added; `update_appointment.php:65` unchanged; whitelist (`:36`) and admin dropdown (`appointments.php:148`) still include `Cancelled`, so reachable (admin-only; students can only create appointments via `save_appointment.php`, never cancel). Falls back to generic email; in-app still created. No behavior change.
- **`payment_rejected`**: Still missing. No template added; `update_payment.php:69` unchanged; whitelist (`:36`) and admin dropdown (`payments.php:170`) still include `Rejected`, so reachable whenever an admin rejects a payment. Falls back to generic email; in-app still created. No behavior change.
- `git log` confirms none of `class/NotificationService.php`, `admin/update_status.php`, `admin/update_appointment.php`, `admin/update_payment.php` changed since Phase 09 — P10-004-07 is intact and accurate, not stale.

### 6. Reachability of Each Missing-Template Type
All three are reachable today, exclusively through admin actions: request `Cancelled` via `admin/request_details.php` form → `admin/update_status.php`; appointment `Cancelled` via `admin/appointments.php` modal → `admin/update_appointment.php`; payment `Rejected` via `admin/payments.php` modal → `admin/update_payment.php`. Each path is CSRF-protected, admin-role-gated, and whitelist-validated, with a visible UI control exposing the status — none requires direct-POST trickery. No student-triggerable path produces any of the three keys. `Cancelled` remains a supported request status end-to-end (whitelist, dropdown, `request_details.php:37` Rejected/Cancelled display branch, dashboard `NOT IN ('Completed','Cancelled')` counts, scheduler `forbiddenStatuses`).

### 7. Security Observations
No security regression found. Adding templates later requires no weakening of anything: all three triggers sit behind POST-only + `validateCsrfToken` + `isLoggedIn` + `$_SESSION['role'] === 'admin'` guards with prepared statements throughout. Email recipients are resolved server-side (`NotificationService.php:58-59`: `SELECT email, first_name FROM users WHERE user_id = ?`) from the request owner's stored row — never from POST data. Callers derive `user_id`/`request_id` from server-side JOINs on the target record, not from client-supplied user identity. `update_status.php` performs no per-request ownership check, but that is correct for an admin endpoint (admins manage all requests). Student-owned paths verified intact: `data/submit_payment.php` scopes by `user_id` (+ P10-007 Paid guard), `data/mark_read.php` enforces `notification_id` + `user_id` ownership (IDOR-safe). In-app message rendering in `notifications.php:30,33` is `htmlspecialchars`-escaped. Pre-existing note (unchanged, out of scope): the generic fallback itself is silent — no log distinguishes a fallback send from a templated send.

### 8. Live/Read-Only Tests Performed
Static/source inspection only — no live test claimed. (a) `php -l` clean on `class/NotificationService.php`, `admin/update_status.php`, `admin/update_appointment.php`, `admin/update_payment.php`, `config/notifications.php`. (b) Repo-wide reference searches confirming exactly 3 `NotificationService` callers, 7 template keys vs 10 emitted email keys, zero student-side notification writers, and zero student-side cancel endpoints. (c) `git status` clean before edit (only this log modified afterward); `git log` shows notification files untouched since Phase 09. No test data created, no records modified, no email sent — live send was deliberately avoided (mail transport behavior already covered by P9-021; template selection is fully determined by static code).

### 9. Findings
1. P10-004-07 CONFIRMED STILL OPEN — all three keys (`request_cancelled`, `appointment_cancelled`, `payment_rejected`) still miss `getTemplate()` and receive the generic `SPVAI Notification` email. Concrete impact: for each of the three transitions, the in-app notification is created normally and an email IS still attempted, but the email carries only the generic subject/body (no reference number, document, date/time, amount, or reason) — degraded information, not a lost notification and not an error.
2. All three triggers are reachable via supported admin UI controls (not dead code).
3. No new missing-template gaps introduced; every other emitted key resolves to a dedicated template.
4. No security regression; future templates need only map entries reusing already-passed `$emailData`.
- **Severity**: Low (same as P10-004-07 — notification delivered, content generic; no auth/data/transaction impact)

### 10. Recommended Next Action
In a future implementation task (NOT this audit): add the three dedicated templates in `getTemplate()` only — `request_cancelled` (reuse `ref`/`doc`/`remarks`), `appointment_cancelled` (reuse `ref`/`doc`/`date`/`time`), `payment_rejected` (reuse `ref`/`amount`/`method` + `remarks`, which the caller does not yet pass and would need adding to `$emailData`). No handler, trigger, status, UI, auth, or schema change is needed. Awaiting owner approval to proceed.
- **Status**: AUDIT COMPLETE (P10-004-07 remains OPEN; nothing fixed, nothing implemented)

---
## P10-016 — Implement Dedicated Notification Email Templates
- **Objective**: Resolve P10-004-07 (confirmed by P10-015 audit) by adding dedicated email templates for `request_cancelled`, `appointment_cancelled`, and `payment_rejected`, preserving the existing notification architecture.
- **Files Modified**:
  - `class/NotificationService.php` (+12 lines): three new entries in `getTemplate()`'s `$templates` map, following the existing subject/body/placeholder conventions exactly (raw interpolation + `??` fallbacks, `[Student Name]` placeholder, `Records Office<br>SPVAI` signature). All 7 pre-existing templates byte-identical; generic fallback line untouched; `notifyUser`/`sendEmailNotification`/deduplication/mail handling untouched.
  - `admin/update_payment.php` (+1 line): added `'remarks' => $remarks` to `$emailData` so the rejection reason reaches the template. `$remarks` is the existing POST-derived variable (line 28) already saved to the DB — no new query, no workflow/status change. Reference number deliberately NOT added (beyond the authorized minimum; `SELECT p.*` already holds it if a future task wants it).
  - No change needed in `admin/update_status.php` (already passes `ref`/`doc`/`remarks`) or `admin/update_appointment.php` (already passes `ref`/`doc`/`date`/`time`); triggers, whitelists, dropdowns, and the `payment_paid → payment_verified` alias all untouched.
- **Templates Added**:
  - `request_cancelled` — subject `SPVAI Document Request Cancelled`; body states the request `ref` was cancelled, names the `doc`, shows `Remarks` (fallback `No remarks provided`), portal next-step line, Records Office signature.
  - `appointment_cancelled` — subject `SPVAI Appointment Cancelled`; body states the appointment for `ref` was cancelled, shows `date`/`time`, rescheduling-contact line, signature. No remarks line (caller does not pass remarks; kept strictly to existing `$emailData` per scope).
  - `payment_rejected` — subject `SPVAI Payment Rejected`; body states the payment for `ref` was rejected, shows `amount`/`method`, shows `Reason` from the newly-passed remarks (fallback `No reason provided`), portal resubmission line, signature.
- **Tests Performed** (all actually run): `php -l` on both modified files (clean) + full-project recursive `php -l` sweep (zero errors); throwaway resolution script (temp dir, deleted after) evaluating the real `$templates` literal with representative `$emailData` — 15/15 PASS: 3 new keys resolve to dedicated templates, all 7 existing keys still dedicated, unknown keys still hit the generic fallback, `payment_rejected` remarks with `&`/`=`/empty/HTML pass through intact, subjects match convention. `git diff` confirms purely additive changes (no existing line altered except the one-line `$emailData` addition).
- **Payment Remarks Transport**: `admin/payments.php` serializes the whole form via jQuery `serialize()` (remarks textarea is inside the form; no manual string concat unlike the fixed P10-004-08 pattern), so `&`/`=` are URL-encoded at transport — no corruption path. Template interpolation is server-side string concat (same as existing `request_rejected`), so special chars cannot break template selection.
- **Live Testing Limitation**: No live end-to-end notification run (no disposable DB records created, no email sent). Template generation/selection: TESTED (above). `mail()` invocation and actual delivery: NOT tested — local XAMPP SMTP is unconfigured (known P9-021 context); delivery remains environment-dependent as before. Business-transaction safety is unchanged by construction: `notifyUser` creates the in-app row first and email soft-fails via `@mail` + log, and neither path was touched.
- **Regression Checks**: P10-005 (sidebar routing), P10-007 (Paid guard), P10-008 (`$minAdvance` both endpoints), P10-009 (`encodeURIComponent`), P10-012 (profile handler) markers all still present (notification files are the only ones touched; `git status` shows exactly `class/NotificationService.php`, `admin/update_payment.php`, this log).
- **Security**: No findings. Recipient still resolved server-side from `users` by `user_id`; no email address from POST; admin/CSRF/prepared-statements/dedup/mail-failure handling untouched; remarks follow the existing raw-interpolation pattern used by all templates (no new escaping model introduced, no new exposure — remarks were already stored and shown in-app/admin views).
- **Status**: FIXED (P10-004-07 resolved; P10-015 audit goal completed)

---
## P10-017 — Legacy / Dead-Code Cleanup Audit (AUDIT ONLY, nothing deleted/renamed/moved)

### 1. Audit Scope
Full read-only inventory: root + `admin/`, `data/`, `class/`, `config/`, `database/`, `interface/`, `layouts/`, `php/`, `admin/modal/`, `assets/`, `css/`, `js/`, `library/`, `images/`. Repo-wide reference searches (filenames, `require/include`, AJAX `url:`, nav `href=`, asset `src|href`, table names in SQL), nav/link reachability traces from both modern layouts, live read-only `SHOW TABLES` on `spvaii`. No files deleted/renamed/moved, no tables touched, no logic changed.

### 2. Modern Active Code (A. ACTIVE MODERN — do not touch in cleanup)
Root: `index.php` (pure router), `public_home.php`, `login.php`, `register.php`, `logout.php`, `student_area.php`, `request_document.php`, `request_confirmation.php`, `my_requests.php`, `request_details.php`, `appointments.php`, `appointment_confirmation.php`, `payments.php`, `notifications.php`, `profile.php`. Admin: `dashboard.php`, `requests.php`, `request_details.php`, `appointments.php`, `payments.php`, `documents.php`, `index.php` (login), `logout.php`, `update_status.php`, `update_appointment.php`, `update_payment.php`, `update_document_fee.php`. Data (all AJAX/form-called by modern pages): `auth_login.php` (student login), `login.php` (admin login + legacy MD5 migration), `register.php`, `create_request.php` (NOT dead — `request_document.php:101`), `get_slots.php` (NOT dead — `appointments.php:99`), `save_appointment.php`, `submit_payment.php`, `mark_read.php`, `update_profile.php`. Infra: `class/Auth.php`, `class/NotificationService.php`, `config/appointments.php`, `config/notifications.php`, `database/Database.php` + `database/Connection.php` (shared chain), all 4 `layouts/*` (sole nav source; zero legacy links), `assets/js/jquery-3.1.1.min.js` + `assets/js/bootstrap.min.js` (loaded by modern footers/login/register/admin-index AND legacy pages — shared), `assets/css/bootstrap.min.css`, `images/spvai.ico`.

### 3. Legacy Still Referenced (B. LEGACY BUT STILL REFERENCED — reachable only inside the legacy module, NOT from modern nav)
| File/Component | Referenced By | Reachable From Modern App? | Classification |
|---|---|---|---|
| `reserved.php` | nothing modern (docs only); requires `data/get_origin.php`, `data/get_destination.php`; AJAX `data/session_itinerary.php` | No | B (legacy entry; internally chained) |
| `accomodation.php` | `data/session_itinerary.php` returns url `accomodation.php`; requires `data/get_all_accomodations.php`; AJAX `data/session_accomodation.php` | No | B |
| `passenger.php` | `data/session_accomodation.php` returns url `passenger.php`; requires `admin/modal/message.php`; AJAX `data/save_booked.php` | No | B |
| `payment.php` (travel) | `data/save_booked.php:35` returns url `payment.php`; requires `data/depart_from_to.php`, `data/get_accomodation.php`, `data/getBooked.php` (x2) | No | B |
| `admin/reservation.php` + `admin/transaction.php` | cross-link each other (Reserved/History tabs); require `admin/session_login.php` + modal files; AJAX `data/get_all_book.php`, `deleteBook.php`, `getBookBy.php`, `save_transaction.php`, `get_all_transaction.php`, `refundTen.php` | No (modern admin nav has Dashboard/Requests/Appointments/Payments/Documents only) | B (self-contained legacy admin subsystem) |
| `admin/session_login.php` | required by the two legacy admin pages only | No | B (legacy guard; note self-`require_once` no-op line 2) |
| `admin/modal/message.php`, `confirmation.php` | required by legacy reservation/transaction (+passenger uses message) | No | B |
| `admin/modal/view_passenger.php` | required by `admin/transaction.php:50` only | No | B |
| `class/Book.php` + `interface/iBook.php` | required by legacy data handlers (`deleteBook`, `getBookBy`, `getPassengers`, `get_all_book`, `save_transaction`); queries `booked` | No | B (legacy shared class) |
| `class/Transaction.php` + `interface/iTransaction.php` | required by `get_all_transaction.php`, `refundTen.php`, `save_transaction.php`; queries `transaction` | No | B (legacy shared class) |
| Legacy data handlers (`save_booked`, `save_transaction`, `deleteBook`, `getBookBy`, `getPassengers`, `get_all_book`, `get_all_transaction`, `refundTen`, `session_accomodation`, `session_itinerary`, `depart_from_to`, `get_origin`, `get_destination`, `get_accomodation`, `get_all_accomodations`, `getBooked`, `getRemainingAcc`) | referenced only by the legacy pages above | No | B (legacy-internal; remove only as a set) |
| `user` (legacy table) | ACTIVE `data/login.php:42,62` legacy-MD5 lookup + post-migration DELETE; `test_auth.php:74,77` harness | YES — modern admin login path | B (see §6; NOT removable while migration code exists) |
| `assets/css/bootstrap-theme.min.css`, `simple-sidebar.css`, `dataTables.bootstrap.min.css`, `assets/js/jquery.dataTables.min.js`, `dataTables.bootstrap.min.js` | loaded only by legacy pages (travel + reservation/transaction) | No | B (legacy-only assets) |

### 4. Likely Dead / Cleanup Candidates (C/D — no modern reference, no modern navigation, not shared infra)
| File/Component | Evidence | Modern Reference? | Classification |
|---|---|---|---|
| `php/notify.php`, `php/sendmail.php` | zero `require`/AJAX/`href` from any page (`sendmail` named only in unloaded `js/common.js`); hardcoded `vijayanpp02@gmail.com` contact-form mailers predating `NotificationService` | None | D. SAFE CANDIDATE (as a pair; nothing includes them) |
| `js/common.js` | references `php/sendmail.php`; not loaded (`<script src>`) by any PHP page | None | D. SAFE CANDIDATE |
| `class/User.php` + `interface/iUser.php` | targets legacy `user` table; only self-instantiation (`new User()` at file bottom); no page/handler requires it (P10-010 confirmed) | None | D. SAFE CANDIDATE (as a pair; table itself stays — see §6) |
| `root css/` (10 files), `root js/` (11 files), `library/` (bootstrap, font-awesome, jquery-1.11.3, prettyPhoto, modernizr, owl, vegas) | zero `src|href` references from any PHP page (legacy pages use `assets/`, modern use Tailwind CDN + `assets/`); `images/spva.jpg|spva1.jpg` referenced only inside dead root `css/` | None | D. SAFE CANDIDATE (whole dirs; legacy theme remnants) |
| `assets/js/admin.js` | loaded by no page; content points at non-existent `data/update_password.php` (P10-010 orphaned legacy JS) | None | D. SAFE CANDIDATE (single file; other `assets/js` files stay) |
| `assets/js/bootstrap.js`, `jquery-1.12.3.js`; `assets/css/bootstrap.css`, `bootstrap-theme.css` (unminified twins) | minified/3.1.1 variants are the loaded ones; twins unreferenced | None | D. SAFE CANDIDATE (keep loaded variants) |
| `assets/css/form-login.css` | no page links it (`form-login` hits are element IDs, not the file) | None | D. SAFE CANDIDATE |
| `test.php` (root, 2-line `uniqid` echo), `data/test.php` (lorem ipsum, not PHP) | unreferenced placeholders | None | D. SAFE CANDIDATE |
| ``data/create_request.php` `` (trailing-backtick stray) | P10-004-09 documented; only `data/create_request.php` is referenced | None | D. SAFE CANDIDATE (delete backtick file only) |
| `C?xampphtdocsSPVAIPHASE9_BUG_LOG.md` (root, 3321 B, 66 lines, P9-001 fragment) | mangled-filename duplicate of Phase 9 log content; unreferenced | None | D. SAFE CANDIDATE (stray doc fragment) |
| `status` (DB table) | exists live; zero `FROM/INTO status` references in any PHP | None | D. candidate at DB level (see §6) |

### 5. Ambiguous Items (E. AMBIGUOUS — OWNER DECISION REQUIRED, do not delete on this audit's authority)
1. Whole legacy travel module + legacy admin subsystem + their tables (`reserved/accomodation/passenger/payment.php`, `admin/reservation.php`, `admin/transaction.php`, `admin/session_login.php`, `admin/modal/*`, `class/Book|Transaction.php`, `interface/iBook|iTransaction.php`, 17 legacy data handlers, tables `booked`, `transaction`, `accomodation`, `destination`, `origin`): individually B, but GROUP removal needs owner sign-off (direct-URL reachability = anyone can still open them; P10-014 noted their Home links now land on the login router). Also `admin/reservation.php:53` requires `admin/modal/view_booker.php`, which DOES NOT EXIST — that legacy page is already fatally broken, supporting dormancy but still owner call.
2. `test_auth.php` (Phase 2 auth harness, writes/reads `user` table), `apply_schema.php`, `database/setup_phase1.php`: unreferenced dev/ops utilities. `apply_schema.php` is web-reachable with NO auth and executes schema SQL — keep-or-restrict is a security decision (see §10), not a plain deletion.
3. `assets/css/input.css` + `package.json`/`node_modules/`/`tailwind.config.js`: Tailwind build pipeline whose output (`output.css`) was never built and no page links; runtime uses CDN. Remove pipeline vs keep for future builds — owner + deployment decision.
4. `spvaii.sql` (root dump), `PHASE*.md` docs, `DEV_ADMIN_LOCAL.md`: reference material, harmless. Keep (owner may archive separately).
5. `user` table retention after the last legacy admin migrates (see §6).

### 6. Legacy Database Tables (live `SHOW TABLES` on `spvaii`, read-only — 13 tables)
| Table | Modern Usage | Legacy Usage | Candidate for Future Removal? | Evidence |
|---|---|---|---|---|
| `users`, `document_types`, `requests`, `appointments`, `payments`, `notifications` | YES — all modern workflows | No | NO (modern core) | phase1_schema + every modern query |
| `user` | YES — `data/login.php:42,62` MD5-migration lookup + delete | `class/User.php`, `test_auth.php` | NO (not while migration code is live; re-audit after removal of that path) | active login file lines cited |
| `booked` | None | `class/Book.php`, `getBooked.php`, `save_booked.php`, `get_all_accomodations.php`, `getRemainingAcc.php`, `session_accomodation.php` | GROUP decision with module (§5.1) | SQL grep; live table present |
| `transaction` | None | `class/Transaction.php`, `save_transaction.php` | GROUP decision with module | SQL grep; live table present |
| `accomodation`, `destination`, `origin` | None | `get_accomodation(s).php`, `get_origin.php`, `get_destination.php`, `depart_from_to.php`, `save_booked.php` | GROUP decision with module | SQL grep; live tables present |
| `status` | None found (zero PHP references of any kind) | None found | YES — cleanest single-table candidate, owner to confirm no external tool reads it | exhaustive `FROM/INTO status|status_id` search empty; live table present |

### 7. Assets / Libraries
Still loaded (KEEP): Tailwind CDN (all modern pages), `assets/js/jquery-3.1.1.min.js` + `assets/js/bootstrap.min.js` (modern footers/login/register/admin-index + legacy pages), `assets/css/bootstrap.min.css` (both worlds), `images/spvai.ico` (all worlds). Legacy-only (see §3/§4): DataTables set, `simple-sidebar.css`, `bootstrap-theme.min.css`. Unloaded (see §4): root `css/`+`js/`+`library/`, `assets/js/admin.js`, unminified twins, `form-login.css`, `js/common.js`. Build pipeline dormant (`input.css` source present, `output.css` never built, CDN used instead) — §5.3.

### 8. Modern Workflow Dependency Check
The modern SPVAI workflow (public_home → login/register → student_area → request → history → details → appointment → payment → notifications → profile; admin login → dashboard → requests/details → appointments → payments → documents) depends on NO legacy travel file, NO legacy data handler, and NO legacy table except `user` (sole exception: the admin-login MD5 migration path in `data/login.php`). Both nav layouts link exclusively to modern pages. Reference direction is one-way legacy→modern (`index.php` Home links) — deleting legacy files cannot break any modern page (no modern `require`/AJAX/`href` points at them; verified by exhaustive search).

### 9. Recommended Cleanup Order (FUTURE task only — NOT implemented)
1. Owner approval gate (especially §5.1 module-group + §5.2 `apply_schema.php` handling). 2. Zero-risk file strays: backtick `data/create_request.php``, mangled Phase-9-log filename, `test.php`, `data/test.php`. 3. Dead pairs: `php/notify.php`+`sendmail.php`, `js/common.js`, `class/User.php`+`interface/iUser.php`, `assets/js/admin.js`, unminified twins, `form-login.css`. 4. Dead dirs: root `css/`, `js/`, `library/` (verify no direct-URL/bookmark reliance first). 5. `status` table (single-table drop, confirm no external readers). 6. LAST: legacy module group (pages + legacy admin + legacy data handlers + Book/Transaction classes + their tables) + `user`-table/migration-path retirement as one coordinated decision. Each step: backup → remove → `php -l` sweep → smoke-test modern student+admin flows → log.

### 10. Security
Two observations (REPORTED ONLY, not fixed): (a) `apply_schema.php` is web-reachable with no authentication and executes `database/phase1_schema.sql` statements on hit — restrict/delete is an owner decision; (b) `php/notify.php`/`sendmail.php` contain a hardcoded third-party gmail and take unvalidated `$_POST` mail input, but are fully unreachable (no page references them) — risk is latent, removed with the files in a future cleanup. Legacy travel/admin pages perform no ownership checks of their own, but they touch only legacy tables and are unreachable from modern nav. No modern auth/session/RBAC/CSRF issue found during this audit.

### 11. Tests
All actually performed, read-only: full directory listings (root, admin, data, class, config, layouts, database, interface, library, assets/css+js, css, js, images, admin/modal); repo-wide greps (legacy filenames; `require/include`; `common.js|admin.js|notify.php`; `create_request|refundTen|…` handler names; `css/|js/|images/` asset refs; `output.css|input.css|tailwind`; all legacy table names in SQL; `FROM status|status_id`; nav `href=` in both layouts); file-head reads (`php/notify.php`, `php/sendmail.php`, `test.php`, `data/test.php`, `apply_schema.php`, `setup_phase1.php`, `session_login.php`, `Connection.php`, `data/login.php`, `phase1_schema.sql`); live read-only `SHOW TABLES` (13 tables, temp script deleted); `git status` confirms working tree otherwise untouched by this audit. No live page loads, no writes, no deletions; `php -l` sweep not re-run (no code modified).

### 12. Files Changed
`PHASE10_BUG_LOG.md` only (this entry). If `git status` shows anything else, it is pre-existing P10-016 work, not this audit.
- **Status**: AUDIT COMPLETE — AWAITING OWNER APPROVAL. NOTHING DELETED, RENAMED, MOVED, OR REWRITTEN.

---
## P10-018 — Web-Reachable Schema / Setup Utility Audit (AUDIT ONLY, nothing executed/modified/moved)

### 1. Scope
Read-only audit of `apply_schema.php`, `database/setup_phase1.php`, `test_auth.php` (full-file reads), plus reference searches, similar-utility sweep, reachability analysis (static/config), `php -l`, and `git status`. The three scripts were NOT executed, no SQL was run from them, no browser HTTP execution test was performed, no records created.

### 2. Utility Inventory
| Utility | Purpose | HTTP Accessible? | Modifies DB? | Auth? | CSRF? | Modern Dependency? | Recommendation |
|---|---|---|---|---|---|---|---|
| `apply_schema.php` (32 lines) | Re-runs `database/phase1_schema.sql` via `Database::insertRow`, plain-text counts | Physically yes (web root, no `.htaccess` anywhere); not linked from any nav | Yes — but current SQL is idempotent (`CREATE IF NOT EXISTS` + `INSERT IGNORE` only) | None | N/A (no state-changing intent beyond schema; no form) | No (unreferenced by all routes) | REMOVE (redundant duplicate of documented `setup_phase1.php`; unneeded post-install) |
| `database/setup_phase1.php` (44 lines) | Same schema run with HTML progress output; documented install step (`PHASE1_DATABASE.md:81`) | Physically yes (subdirs are web-served too); not linked from any nav | Same idempotent set as above | None | N/A | No runtime dependency (one-time setup only) | MOVE outside web root (preserves reinstall capability, removes HTTP exposure) |
| `test_auth.php` (88 lines) | Phase 2 auth test harness: registers test users, duplicate-checks, login checks, legacy MD5 migration simulation | Physically yes; not linked from any nav | YES — INSERTs `users` + `user` rows on EVERY hit, no cleanup | None | None | No | REMOVE (known-credential admin creation, see §5) |

### 3. apply_schema.php
Reads the fixed file `database/phase1_schema.sql` (no `$_GET/$_POST`/request input of any kind — SQL cannot be user-influenced), splits on `;`, executes each chunk through PDO `prepare/execute` (`Database::insertRow`), prints success/error counts. Current SQL file contains only `CREATE TABLE IF NOT EXISTS` (6 modern tables) and `INSERT IGNORE` seeds — no DROP/DELETE/TRUNCATE/ALTER/UPDATE — so a hit today re-applies no-ops. Concrete exposures: (a) any visitor can trigger it (GET, no login); (b) per-query PDO exception text is echoed to the browser (SQL error disclosure). Destructive capability with the CURRENT sql file: none; the risk is exposure + re-runnability, not data loss. Redundant: byte-equivalent purpose to `setup_phase1.php`.

### 4. setup_phase1.php
Same mechanism and same SQL source as §3 (relative `phase1_schema.sql`, resolves on direct URL hit), with HTML output that additionally echoes the first 50 chars of each query plus error text (wider error/query disclosure than `apply_schema.php`). This is the documented install procedure, so it had an operational purpose at install time; post-install it serves none. Same auth posture (none) and same idempotent-impact profile. Relative `require_once('Database.php')` works on direct hit.

### 5. test_auth.php
Highest-concern finding, established statically (script NOT executed): every load performs live writes — Test 1 INSERTs a `users` row (random test student), Test 7 INSERTs a legacy `user` row AND a `users` row with `role='admin'`, email `migration_test@spvai.edu.ph`, password = known literal `admin123` (lines 69-81), with no cleanup and no uniqueness guard on repeat hits beyond UNIQUE-constraint failures. I.e., an unauthenticated GET creates a working admin account with public credentials (first hit succeeds; later hits fail loudly but the account persists). It also drives `$_SESSION` via `Auth::authenticate`. No login, no role check, no CSRF, no method restriction. Not referenced by any route, form, JS, or doc procedure (only mentioned descriptively in `PHASE2_AUTHENTICATION.md:44` and the P10-010/011 evidence lists).

### 6. Similar Utilities
Swept `setup|install|migrat|seed|debug|diagnos|repair|reset|fix|drop|truncate` + `file_get_contents|exec|shell_exec|system|passthru|eval|.sql` across all PHP: the ONLY SQL-file executors are the two §3/§4 scripts; no shell-exec/eval anywhere. `test.php` (2-line uniqid echo) and `data/test.php` (lorem ipsum) are inert. `Connection.php:38-39` debug lines are commented out. `spvaii.sql` (6680 B root dump of legacy table structures, e.g. `accomodation`) is inert reference material, not executed by any code (Apache may serve it as text — ownerURL hygiene note only).

### 7. HTTP Reachability
Physically accessible: YES for all three — no `.htaccess` exists anywhere in the project (glob confirms), standard XAMPP serves every `.php` under web root including `database/`, so `/SPVAI/apply_schema.php`, `/SPVAI/database/setup_phase1.php`, `/SPVAI/test_auth.php` are directly requestable. Navigationally reachable: NO — zero links/forms/AJAX/docs-procedures (except the setup doc's one-time install instruction) point at them. No HTTP execution test was performed; the conclusion rests on file placement + absence of access controls, which is conclusive for physical reachability.

### 8. Database Modification Capability
`apply_schema.php` / `setup_phase1.php`: CAN execute whatever `phase1_schema.sql` contains — currently only idempotent CREATE-IF-NOT-EXISTS + INSERT-IGNORE (verified by full read of the 133-line schema file in P10-017). `test_auth.php`: DOES write on every load (test users + known-credential admin, §5). None accepts user input into SQL (all statements use bound params or fixed file content).

### 9. Authentication/Authorization/CSRF
All three: no `isLoggedIn`, no `requireRole`, no CSRF token, no method check (all run on plain GET). CSRF is moot (no session-victim flow — damage is direct, not forged). Session impact: only `test_auth.php` touches `$_SESSION` (via `authenticate`). Error disclosure: `apply_schema.php` echoes exception messages; `setup_phase1.php` echoes query fragments + exceptions; `test_auth.php` echoes failure messages including exception text.

### 10. Modern Dependency Check
The current SPVAI application does NOT depend on any of the three at runtime: no `require/include`, no AJAX `url:`, no form action, no nav link in either layout or any page references them (exhaustive filename + basename grep; only doc mentions and this log). Public/student/admin flows operate entirely without them. `setup_phase1.php` retains one-time operational value for fresh installs (hence MOVE, not REMOVE).

### 11. Risk Assessment (concrete, no inflation)
- `test_auth.php`: HIGH if left web-reachable — unauthenticated creation of a known-password admin account plus junk rows per hit (static certainty; never executed to prove it). Latent only while the URL is unvisited, but trivially discoverable.
- `apply_schema.php` / `setup_phase1.php`: LOW with current SQL (idempotent re-run + error/query disclosure to anonymous visitors). Would escalate only if the SQL file ever gains destructive statements — a change-control note, not a current impact.
- No evidence of prior exploitation was sought or found (out of scope; logs not inspected).

### 12. Recommended Actions (NOT implemented — owner approval required)
- `test_auth.php` → REMOVE (delete in a controlled task after confirming no dev still needs it; CLI re-creation is trivial if ever required).
- `apply_schema.php` → REMOVE (redundant with the documented `setup_phase1.php`; serves no post-install purpose).
- `database/setup_phase1.php` → MOVE outside web root (keeps fresh-install capability, kills HTTP exposure; update `PHASE1_DATABASE.md` install step accordingly). DISABLE acceptable alternative.
- Incidental: consider suppressing direct `.sql` serving when hardening (incidental hygiene, owner call).

### 13. Owner Decisions Required
Approve each recommendation above (REMOVE / REMOVE / MOVE), plus whether to inspect access logs for prior hits to `test_auth.php` (suggested, not performed), and whether any freshly-installed environments still need the setup script before it is moved.
- **Status**: AUDIT COMPLETE — SECURITY ISSUE DOCUMENTED, NOT FIXED. NOTHING EXECUTED, MOVED, OR DELETED.

---
## P10-019 — Security Utility Cleanup & Installer Relocation
- **Status**: FIXED / COMPLETE
- **Pre-change verification**: Re-read all three utilities and re-ran repo-wide reference searches — confirmed NO PHP `require/include`, AJAX, form, nav, or JS references to `test_auth.php`, `apply_schema.php`, or `setup_phase1.php` (only doc mentions + prior log entries). No unexpected runtime dependency found, so implementation proceeded.
- **Files Removed**: `test_auth.php` (deleted — unauthenticated web-accessible harness that INSERTed test users plus a known-credential admin account on every load; HIGH risk eliminated at the source). `apply_schema.php` (deleted — redundant, unauthenticated browser-accessible schema re-runner; documented setup path already existed elsewhere). Neither was replaced; no new test/schema endpoint was introduced under any name.
- **Files Moved**: `database/setup_phase1.php` → `C:\xampp\SPVAI-setup\setup_phase1.php` (outside the Apache document root `C:\xampp\htdocs`, hence not HTTP-accessible by construction). Body byte-identical except a header comment + two `__DIR__`-anchored path adaptations (`Database.php` require, `phase1_schema.sql` path) so it stays runnable via CLI (`php C:\xampp\SPVAI-setup\setup_phase1.php`). No schema/query/output logic changed. `php -l` clean on the moved copy.
- **Files Modified (docs only)**: `PHASE1_DATABASE.md` setup section now documents CLI execution from the new location, one-time/manual-only status, and warns against copying it back into the web root (incl. `apply_schema.php` removal note). `PHASE2_AUTHENTICATION.md` `test_auth.php` line marked REMOVED-in-P10-019 (historical record retained, clearly deprecated). No application code touched.
- **Security Result**: The HIGH-risk `test_auth.php` exposure is eliminated — the file no longer exists under the web root, so no request can trigger its account-creating code path. Schema re-execution is no longer browser-triggerable from any path (`apply_schema.php` gone, `setup_phase1.php` outside docroot).
- **Web Accessibility Result (live HTTP-tested, Apache running)**: `/SPVAI/test_auth.php` → 404, `/SPVAI/apply_schema.php` → 404, `/SPVAI/database/setup_phase1.php` → 404. Nothing was executed to prove this — 404 on the deleted paths is the expected and sufficient signal.
- **Reference Verification**: Post-change repo-wide grep for `test_auth|apply_schema|database/setup_phase1` in `*.php` returns zero application references (only `.md` history + this log). `git status` shows exactly: `D apply_schema.php`, `D database/setup_phase1.php`, `D test_auth.php`, `M PHASE10_BUG_LOG.md`, `M PHASE1_DATABASE.md`, `M PHASE2_AUTHENTICATION.md`.
- **PHP Syntax**: Full-project recursive `php -l` sweep — ZERO errors. Moved copy individually clean.
- **Regression Smoke Test (live HTTP)**: `public_home.php` → 200 landing; `index.php`, `admin/dashboard.php`, `student_area.php` logged-out → 302 redirects (guards intact). No missing includes possible: no modern file ever included the removed/moved utilities (verified pre- and post-change).
- **Database Safety**: NO database writes performed by P10-019 — no INSERT/UPDATE/DELETE/DDL; the moved setup script was NOT executed; `phase1_schema.sql` untouched. All DB contact was read-only (`SHOW TABLES` lineage + the SELECTs below).
- **Existing Test Admin Account**: REPORTED, NOT REMOVED (per approval rule). Read-only check confirms BOTH rows still exist: `users` id=4 (`migration_test@spvai.edu.ph`, role=admin) and legacy `user` id=3 (`migration_test`); `USERS_TOTAL=5`. Deleting either row requires separate owner approval — recorded here as a remaining observation, not actioned.
- **Access Log Recommendation**: Owner should review Apache access logs for historical hits to `/SPVAI/test_auth.php` (would indicate past harness runs and possible test-account creation). No exploitation is claimed — logs were not inspected in this phase.
- **Scope Check**: No other files removed, no legacy travel/DB-table cleanup, no auth/RBAC/CSRF/schema/workflow/UI changes. Strictly the three P10-018 recommendations.
