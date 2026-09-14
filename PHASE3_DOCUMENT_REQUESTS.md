# PHASE 3: DOCUMENT REQUEST SYSTEM DOCUMENTATION

This document describes the implementation of the core document request system for students.

## 1. Request Workflow
The student request process has been transformed from a travel booking flow into a records-centric flow:
1. **Authentication**: Student logs in $\rightarrow$ redirected to `student_area.php`.
2. **Selection**: Student visits `request_document.php` $\rightarrow$ Views active documents from `document_types`.
3. **Submission**: Student fills a form (Purpose, Copies) $\rightarrow$ AJAX call to `data/create_request.php`.
4. **Confirmation**: Student is redirected to `request_confirmation.php` with a unique reference number.
5. **Tracking**: Student visits `my_requests.php` $\rightarrow$ Views a list of their requests and their current status.
6. **Details**: Student clicks "Details" $\rightarrow$ `request_details.php` shows full request info and admin remarks.

## 2. Database Implementation
The system utilizes the `requests` table established in Phase 1.

- **Ownership**: Every record in `requests` is linked to a `user_id` from the `users` table.
- **Document Link**: Every request links to a `document_id` from the `document_types` table.
- **Initial State**: All new requests are created with the status `Pending`.
- **Reference Numbers**: Generated using the format `SPVAI-[YEAR]-[7-digit-ID]` (e.g., `SPVAI-2026-0000105`).

## 3. Validation & Security

### Server-Side Validation
- **Document Validity**: The system verifies that the `document_id` exists and is marked as `active = 1`.
- **Copies**: Enforces an integer range of 1 to 10.
- **Purpose**: Required field; cannot be empty.
- **CSRF Protection**: All submissions use the CSRF tokens established in Phase 2.

### Access Control & IDOR Protection
- **Authentication**: All request pages (`request_document.php`, `my_requests.php`, `request_details.php`) call `$auth->requireRole('student')`.
- **Request Ownership**: The `my_requests.php` page filters the database query by the current session's `user_id`.
- **Direct Access Prevention**: `request_details.php` verifies that the requested `request_id` belongs to the logged-in `user_id` before displaying any data.

### SQL & Output Security
- **PDO**: All queries use prepared statements via the `Database` class.
- **XSS Prevention**: All user-provided and database-retrieved content is escaped using `htmlspecialchars()` before being rendered in HTML.

## 4. Files Created
- `request_document.php`: UI for listing and requesting documents.
- `request_confirmation.php`: UI for request success confirmation.
- `my_requests.php`: UI for student request history.
- `request_details.php`: UI for individual request details.
- `data/create_request.php`: Backend handler for creating requests.

## 5. Files Modified
- `student_area.php`: Updated navigation to include "Request Document" and "My Requests".

## 6. Legacy Files Preserved
The following legacy booking files remain untouched and functional:
- `reserved.php`, `accomodation.php`, `passenger.php`, `payment.php`.

## 7. Integration Status
- **Payment**: Compatible with the `payments` table. A request is created first; payment will be handled in a future phase.
- **Appointment**: Compatible with the `appointments` table. Scheduling will be implemented in a future phase.

## 8. Future Work
- Implement Admin Request Management (Phase 5).
- Implement Appointment Scheduling (Phase 4).
- Implement Payment Verification and Tracking (Phase 6).
- Implement Notification System (Phase 7).
