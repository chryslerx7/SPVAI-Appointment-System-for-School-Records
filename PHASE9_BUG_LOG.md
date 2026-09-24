# Phase 9 Bug Log

This file records all bugs identified during the Phase 9 Full System Testing and Bug Fixing phase.

## Bug Format
- **Bug ID**: [P9-XXX]
- **Description**: Brief description of the bug.
- **Severity**: [CRITICAL | HIGH | MEDIUM | LOW | COSMETIC]
- **Steps to Reproduce**:
    1. ...
    2. ...
- **Expected**: What should happen.
- **Actual**: What actually happens.
- **Root Cause**: Why it is happening.
- **Fix**: How it was fixed.
- **Retest**: Result of retesting.
- **Status**: [OPEN | FIXED | VERIFIED]

---
## P9-013 — Admin list pages parse errors fixed
- **Description**: `admin/requests.php`, `admin/appointments.php`, and `admin/payments.php` each failed with `PHP Parse error: syntax error, unexpected end of file`, so Manage Requests / Appointments / Verify Payments (plus dashboard View Schedule / Verify Now targets) returned HTTP 500.
- **Severity**: Critical
- **Steps to Reproduce**:
    1. `php -l admin/requests.php` (same for appointments/payments) — parse error.
    2. Visit `http://localhost/SPVAI/admin/requests.php` (or appointments/payments) — white screen / HTTP 500.
- **Expected**: The three admin list pages render (or redirect to admin login when unauthenticated).
- **Actual**: PHP fatal parse error; page never renders.
- **Root Cause**: Missing closing `<?php endif; ?>` for the outer `if (empty(...)) / else` block after the inner `foreach ... endforeach` in all three files.
- **Fix**: Added exactly one line — `<?php endif; ?>` — after `<?php endforeach; ?>` and before `</tbody>` in each of the three files. No UI, query, auth, or route changes.
- **Retest**: `php -l` passes on all three files; full-project `php -l` sweep shows zero syntax errors; HTTP requests to all three pages now return 302 (auth-guard redirect) instead of 500, matching `admin/dashboard.php` behavior.
- **Status**: FIXED

---
## P9-014 — Admin authentication redirect fixed
- **Description**: Logged-out visits to any admin-protected page redirected to `http://localhost/SPVAI/admin/login.php`, which does not exist (HTTP 404). The real admin login page is `http://localhost/SPVAI/admin/index.php`. Separately, a successful admin login returned JSON `url: 'admin/dashboard.php'`, which from `admin/index.php` resolves to the nonexistent `admin/admin/dashboard.php`.
- **Severity**: High
- **Steps to Reproduce**:
    1. While logged out, visit `http://localhost/SPVAI/admin/dashboard.php` (or requests/appointments/payments) — 302 to `/SPVAI/admin/login.php` → 404.
    2. Log in via `admin/index.php` — success URL points to `admin/admin/dashboard.php` → 404.
- **Expected**: Logged-out admin pages redirect to the existing `/SPVAI/admin/index.php`; successful admin login lands on `/SPVAI/admin/dashboard.php`.
- **Actual**: Redirects pointed at nonexistent paths.
- **Root Cause**: `layouts/admin_header.php` called `$auth->requireRole('admin')` with the default `login.php` redirect, which resolves relative to `/admin/`; and `data/login.php` returned a root-relative-style success URL from inside `/admin/`.
- **Fix**: `layouts/admin_header.php` now calls `$auth->requireRole('admin', 'index.php')` (same explicit pattern already used by `admin/session_login.php`); `data/login.php` success URLs changed from `'admin/dashboard.php'` to `'dashboard.php'` (3 occurrences; sole consumer is `admin/index.php`). No new files, no guard removal, no RBAC/auth-logic change, `class/Auth.php` and student flow untouched.
- **Retest**: `php -l` clean on all touched/related files; logged-out curl on dashboard/requests/appointments/payments/request_details all 302 → `/SPVAI/admin/index.php`; `/SPVAI/login.php` and `/SPVAI/admin/index.php` return 200; old `/SPVAI/admin/login.php` confirmed 404 (never existed); both login handlers verified end-to-end with valid session+CSRF (bad creds correctly rejected); denial target `index.php?error=unauthorized` returns 200.
- **Status**: FIXED

---
## P9-015 — Responsive Student Navigation
- **Description**: Student sidebar was desktop/static only — full-width stacked nav on tablet/mobile with no hamburger, no collapsible menu, no backdrop, and logout buried in the stacked flow.
- **Severity**: High
- **Root Cause**: `layouts/student_header.php` rendered a static `w-full md:w-64` sidebar with no toggle markup, no off-canvas behavior, and no menu script.
- **Files modified**: `layouts/student_header.php` (mobile top bar + hamburger, backdrop, off-canvas sidebar classes, close button, `mt-auto` logout pinning), `layouts/student_footer.php` (small vanilla toggle script only).
- **Behavior**: Desktop unchanged (static `md:w-64` sidebar, same links/routes/logout). Below `md`, a sticky brutalist top bar shows a hamburger (`aria-expanded`/`aria-controls`); tapping opens a `w-64 max-w-[85vw]` slide-in panel with backdrop, body scroll-lock, and logout pinned at panel bottom via existing flex column + `mt-auto`. Closes via close button, backdrop click, nav-link tap, Escape, or resize to desktop; focus moves to close on open and back to hamburger on close.
- **Testing**: `php -l` clean on both files + full-project sweep zero errors; inline script passes `node --check`; 16/16 headless behavior checks pass (open/close/backdrop/Escape/link mobile-vs-desktop/resize reset/no-duplicate-listeners); all 10 student pages still execute layout (302 → `login.php` logged-out, no fatals); no new dependencies (Tailwind Play CDN + existing classes only).
- **Limitations**: Verified by code/HTTP/headless-DOM checks; physical-device and DevTools-width sweeps (320/375/390/430/768/820/1024/1280+) still recommended on your side.
- **Status**: FIXED

---
## P9-016 — Student Request Details Cleanup
- **Description**: (a) No payment deep-link contract existed — payment notifications linked to `request_details.php?id=X`, which showed zero payment info. (b) Reported unclosed `<div>` — stack-trace audit instead found tags balanced but the Appointment grid closed early, pushing Office Remarks outside the 2-column grid.
- **Severity**: Medium
- **Root Cause**: Page had no payment section/query handling; a premature `</div>` ended the `md:grid-cols-2` grid after the appointment column instead of after both columns.
- **Files modified**: `request_details.php` (latest-payment query scoped by validated ownership; compact brutalist Payment card with `id="payment-section"` + Payment Center link; grid close relocated after both columns; tiny `?section=payment` scroll/focus script), `notifications.php` (payment-type `payment_*` View Request links append `&section=payment`; all other types unchanged).
- **Behavior**: `request_details.php?id=X` renders exactly as before plus a Payment card; `?id=X&section=payment` (or `#payment-section`) scrolls to/focuses it. No payment/admin/auth logic changed; skeleton nesting otherwise identical to original.
- **Testing**: `php -l` clean + full-project sweep zero errors; div stack-trace depth 0 with no negatives; deep-link script 4/4 headless checks (scroll/focus on `section=payment`, no-op otherwise); logged-out HTTP on both pages and the deep-link URL all 302 → `login.php` with no fatals; P9-015 markup/script verified intact.
- **Limitations**: Verified by code/HTTP/headless-DOM checks; logged-in visual check of the Payment card and anchor scroll recommended on your side.
- **Status**: FIXED

---
## P9-017 — Admin Payment Request Filter
- **Description**: `admin/request_details.php` links to `admin/payments.php?request_id=<ID>`, but `admin/payments.php` never read `request_id` — the filter was silently ignored and the full list always rendered.
- **Severity**: Medium
- **Root Cause**: Page only handled `search`/`sort`/`order`; no `request_id` intake, validation, or query condition existed.
- **Files modified**: `admin/payments.php` only (producer link already correct, preserved as-is).
- **Behavior**: `?request_id=<positive int>` adds an `AND r.request_id = ?` condition (composes with existing search); any other value (abc, 0, negative, SQL text, absent) is ignored and the page behaves exactly as before, including the existing empty state. Filter form preserves `request_id` via hidden input; Reset/Clear drops it. Tiny active-filter notice added; no UI redesign.
- **Validation/security**: `FILTER_VALIDATE_INT` with `min_range=1`, bound placeholder only (no concatenation), auth guard/`requireRole` untouched, outputs int-cast, update/verify logic and student access unchanged.
- **Testing**: `php -l` clean + full-project sweep zero errors; validation harness 10/11 exact (11th: `' 4 '` trims to 4 — safe, still a genuine int); live-DB transient-row test (rolled back, count restored to 0): request_id=1 → 1 row, =2 → 0 rows, =9999 → 0 rows, search+request combo → correct; logged-out HTTP on all three URL variants 302 → `/SPVAI/admin/index.php`.
- **Limitations**: No logged-in browser session available; authenticated visual check of the filtered list recommended on your side.
- **Status**: FIXED

---
## P9-018 — Document Fee Management / Payment Workflow
- **Description**: All `document_types` fees were 0.00 and no admin UI could change them, while student `payments.php` only lists requests with `fee > 0` — the payment workflow was unreachable via UI.
- **Severity**: Medium
- **Root Cause**: Seed fees all 0.00; fee column existed but had no management interface.
- **Files modified**: `admin/documents.php` (new: fee table, per-row save, read-only name/description/days/status), `admin/update_document_fee.php` (new: POST handler), `layouts/admin_header.php` (one Documents nav link), no schema change, no gateway, payment methods/statuses unchanged.
- **Security**: Page via `requireRole('admin')`; handler enforces POST + CSRF + admin role (same order as `update_payment.php`), `document_id` strictly int-validated with existence check, fee regex `^\d+(\.\d{1,2})?$` capped to DECIMAL(10,2) max, all prepared placeholders, escaped outputs. Students denied by the shared guard.
- **Validation**: 21/21 harness cases pass (25.00/50/0.00/50.50/99999999.99 accepted; negatives, non-numeric, malformed, overflow, bad document_ids rejected).
- **Persistence/integration**: Temp COE fee 50.00 via handler-identical prepared UPDATE → student payment gate returned both COE requests at 50.00 (no `payments.php` changes needed); logged-out `documents.php` 302 → `/SPVAI/admin/index.php`; handler rejects sessionless POST at CSRF gate. No school fees invented.
- **Test data restored**: Yes — all fees back to 0.00, verified (`final_fees_0.00,0.00,0.00,0.00`), student gate back to 0 payable.
- **Limitations**: No logged-in browser session; authenticated visual check + a real admin fee entry recommended on your side.
- **Status**: FIXED

---
## P9-021 — Email warning polluting JSON responses
- **Symptom**: Admin status updates succeeded in DB (visible both sides) but UI showed "An error occurred".
- **Root Cause**: `NotificationService::sendEmailNotification()` called native `mail()` with no local SMTP (`localhost:25` refused), emitting an E_WARNING that `display_errors=STDOUT` wrote into the HTTP body ahead of the valid JSON; jQuery `dataType:'json'` then failed parsing and ran the error callback.
- **Files modified**: `class/NotificationService.php` only (suppressed `mail()` warning via `@`, explicit `error_get_last()` logging on failure, unchanged soft-fail/return semantics).
- **Testing**: `php -l` clean + full-project sweep zero errors; live Approved/Rejected(+reason)/Processing/Ready/Completed updates all returned pure `{"valid":true,…}` with zero warning bytes; DB + notification rows verified per transition; appointment (Confirmed) and payment (Paid) notification paths also return clean JSON.
- **Test data restored**: Yes — request 1 back to Approved + original remarks, appointment 1 back to Scheduled, transient payment + probe notifications deleted (verified).
- **Note**: Local email delivery still fails without SMTP configuration — by design it is logged and does not affect the business operation or response.
- **Status**: FIXED
