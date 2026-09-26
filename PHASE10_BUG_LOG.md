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
