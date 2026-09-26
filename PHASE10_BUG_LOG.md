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
