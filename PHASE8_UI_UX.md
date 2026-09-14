# Phase 8: Complete UI/UX Modernization
## Design System: Modern Digital Brutalism

This phase transforms the SPVAI frontend from a legacy Bootstrap template to a modern, high-contrast Digital Brutalist interface using Tailwind CSS.

## 1. Design Direction
- **Visual Style**: Bold, raw, functional, and structured.
- **Core Philosophy**: Prioritize clarity and usability through strong borders, high contrast, and a physical feel (hard shadows).

## 2. Design Tokens

### Colors
- **Background**: `bg-gray-50` (Off-white)
- **Text**: `text-black`
- **Primary Accent**: `bg-yellow-400` / `border-yellow-400`
- **Status Accents**:
    - Pending: `bg-amber-400`
    - Approved: `bg-blue-500`
    - Rejected: `bg-red-500`
    - Processing: `bg-indigo-500`
    - Ready: `bg-green-500`
    - Completed: `bg-green-800`

### Typography
- **Headings**: Bold, heavy weight, strong hierarchy.
- **Labels**: Uppercase where appropriate for structural clarity.

### Borders & Shadows
- **Borders**: `border-2` or `border-4` using `border-black`.
- **Shadows**: Hard offset shadows (e.g., `shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]`).
- **Radius**: `rounded-none` for primary components.

## 3. Implementation Strategy
1. **Tailwind Integration**: Initialize Tailwind CSS using a build process.
2. **Global Layouts**: Create separate layout shells for Student and Admin portals.
3. **Iterative Page Migration**:
    - Auth Pages (Login, Register)
    - Student Dashboard & Portal
    - Document Request Flow
    - Appointments & Payments
    - Notifications & Profile
    - Admin Dashboard & Management
4. **Regression Testing**: Verify all Phase 1-7 backend logic remains functional.
5. **Bootstrap Cleanup**: Identify and remove obsolete CSS/JS.

## 4. Progress Tracking

| Component | Status | Notes |
| :--- | :--- | :--- |
| Tailwind Setup | ✅ Complete | Integrated via Play CDN for rapid prototyping and layout consistency. |
| Login Page | ✅ Complete | Redesigned with brutalist card and high-contrast inputs. |
| Register Page | ✅ Complete | Redesigned with structured sections and brutalist styling. |
| Student Dashboard | ✅ Complete | Implemented summary stats, recent activity, and profile card. |
| Document Request | ✅ Complete | Redesigned document selection and custom brutalist modal. |
| Appointments | ✅ Complete | Redesigned scheduling flow and date/time selection. |
| Payments | ✅ Complete | Created new payment tracking and submission interface. |
| Notifications | ✅ Complete | Redesigned as a modern brutalist activity feed. |
| Admin Dashboard | ✅ Complete | Implemented high-contrast metric panels and action buttons. |
| Admin Requests | ✅ Complete | Redesigned management table with sticky header and filters. |
| Admin Request Details | ✅ Complete | Implemented detailed view with status timeline and admin controls. |
| Admin Appointments | ✅ Complete | Redesigned appointment list and status management modal. |
| Admin Payments | ✅ Complete | Redesigned verification table and status update modal. |

## 5. Files
### Created
- `tailwind.config.js`
- `assets/css/input.css`
- `assets/css/output.css`
- `layouts/student_layout.php`
- `layouts/admin_layout.php`

### Modified
- (To be updated)

### Deleted
- None (until final cleanup)
