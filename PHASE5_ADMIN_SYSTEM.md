# PHASE 5: RECORDS OFFICE ADMIN SYSTEM DOCUMENTATION

This document describes the implementation of the administration system for the SPVAI Records Office.

## 1. Implemented Features

### Admin Dashboard
- **Overview**: A high-level summary of the system's current state.
- **Real-time Metrics**: Displays counts for Pending/Approved/Rejected requests, total scheduled appointments, today's appointments, and pending payment verifications.
- **Quick Links**: Direct navigation to critical management areas.

### Request Management
- **Request List**: A comprehensive table of all submitted requests with search, sorting (by date, status, doc), and pagination.
- **Request Details**: A detailed view of a specific request including student identity, document info, and current status.
- **Status Updates**: Ability for admins to move requests through the lifecycle (Pending $\rightarrow$ Approved $\rightarrow$ Processing $\rightarrow$ Ready $\rightarrow$ Completed).
- **Rejection Logic**: When rejecting a request, admins can provide mandatory remarks to explain the decision to the student.

### Appointment Management
- **Appointment Queue**: A list of all scheduled appointments linked to their respective requests and students.
- **Status Control**: Ability to update appointment statuses (e.g., Confirmed, Completed, No Show).
- **Integration**: Appointments are managed as an extension of the request, preserving the ownership and validity logic from Phase 4.

### Payment Management
- **Payment Verification**: A dedicated interface to review payment submissions.
- **Verification Workflow**: Admins can mark payments as `Paid` or `Rejected` after verifying the reference number.
- **Audit Trail**: Stores the `verified_by` (Admin ID) and `verified_at` (timestamp) for every payment update.

## 2. Files Created

- `admin/dashboard.php`: The main admin landing page.
- `admin/requests.php`: The request listing and filtering page.
- `admin/request_details.php`: The detailed view for managing individual requests.
- `admin/update_status.php`: Backend handler for updating request statuses.
- `admin/appointments.php`: The appointment management list.
- `admin/update_appointment.php`: Backend handler for updating appointment statuses.
- `admin/payments.php`: The payment verification list.
- `admin/update_payment.php`: Backend handler for verifying payments.

## 3. Files Modified

- `data/login.php`: Updated to redirect authenticated admins to `admin/dashboard.php`.
- `admin/index.php`: Updated login UI to support CSRF tokens and the new authentication flow.

## 4. Database Changes
No schema changes were required for Phase 5. The system utilizes the `users`, `requests`, `appointments`, and `payments` tables established in Phase 1.

## 5. Admin Workflow
1. **Admin Login**: Authenticate as a user with `role = admin`.
2. **Triage**: Use the Dashboard to identify pending requests or payments.
3. **Review**: Open a request in `request_details.php` $\rightarrow$ Review purpose and student info.
4. **Action**: Update request status to `Approved` or `Rejected` (with reason).
5. **Verify**: Review payment reference numbers in `payments.php` $\rightarrow$ Mark as `Paid`.
6. **Coordinate**: Manage appointment statuses in `appointments.php` based on student arrivals.

## 6. Security Measures
- **Role Enforcement**: Every admin page calls `$auth->requireRole('admin')` on the server side.
- **CSRF Protection**: All status updates and verification actions use the CSRF token system.
- **IDOR Protection**: Admin views verify record existence and valid admin session.
- **Input Validation**: Status updates are checked against a server-side whitelist.
- **PDO**: All database operations use prepared statements.
- **Output Escaping**: `htmlspecialchars()` is used on all displayed user and database content.

## 7. Testing Results
- **Admin Auth**: Verified students cannot access admin pages; admins can.
- **Request Status**: Verified that rejecting a request without a reason is blocked.
- **Payment Verification**: Verified that updating a payment to `Paid` correctly records the admin ID.
- **Appointment Sync**: Verified that updating an appointment status is reflected in the request details.
- **Search/Filter**: Verified that searching for student names and reference numbers works across the admin panels.

## 8. Legacy Dependencies
Legacy travel-booking files (`reserved.php`, `accomodation.php`, etc.) and legacy tables (`booked`, `transaction`) remain untouched to preserve backward compatibility. The new admin system operates entirely on the request-centric architecture.

## 9. Known Limitations
- **Notifications**: The admin can update statuses, but the system does not yet automatically send email/SMS alerts to students.
- **Complex Scheduling**: Admin cannot yet reschedule appointments through the UI; they must update status or use the DB.
- **Reporting**: No advanced reporting (e.g., monthly request volume) is implemented.
