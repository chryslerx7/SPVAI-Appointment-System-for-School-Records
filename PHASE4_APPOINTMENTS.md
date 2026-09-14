# PHASE 4: APPOINTMENT SCHEDULING SYSTEM DOCUMENTATION

This document describes the implementation of the appointment scheduling system for students.

## 1. Appointment Workflow
The student appointment process is integrated into the document request lifecycle:
1. **Request Creation**: Student creates a document request (Phase 3).
2. **Eligibility Check**: The system verifies the request belongs to the user and has a schedulable status (e.g., 'Pending', 'Approved').
3. **Date Selection**: Student selects a date via `appointments.php`. The system prevents selecting past dates or weekends.
4. **Slot Selection**: The system fetches available time slots for the selected date via `data/get_slots.php`.
5. **Confirmation**: The student confirms the appointment. The system verifies capacity at the moment of insertion to prevent overbooking.
6. **Tracking**: Appointment details are displayed in `request_details.php` and the student's request history.

## 2. Database Structure
The system uses the `appointments` table established in Phase 1:
- `appointment_id`: Primary Key.
- `request_id`: Foreign Key to `requests`.
- `appointment_date`: DATE.
- `appointment_time`: TIME.
- `status`: Initialized to `Scheduled`.
- `remarks`: Admin notes.

## 3. Date & Time Rules

### Date Rules
- **Past Dates**: Prevented. Only today or future dates are selectable.
- **Weekends**: Prevented. Appointments are only allowed on weekdays (Monday to Friday).
- **Min Advance**: Currently allowed for today, but configurable.

### Time Slot Rules
Slots are generated dynamically based on `config/appointments.php`:
- **Office Hours**: 09:00 AM to 04:00 PM.
- **Slot Duration**: 30 minutes.
- **Capacity**: Maximum 5 appointments per slot.

## 4. Availability & Conflict Prevention
Availability is calculated as:
`Remaining = Max Capacity (5) - Active Appointments on that Date/Time`

**Overbooking Protection**:
The system uses a database transaction in `data/save_appointment.php`. It re-verifies the current count of active appointments for the specific slot immediately before the `INSERT` operation to prevent race conditions.

## 5. Security & Authorization
- **Authentication**: All scheduling pages require a logged-in student session (`$auth->requireRole('student')`).
- **Ownership**: The system verifies that the `request_id` being scheduled belongs to the authenticated `user_id`.
- **CSRF Protection**: Appointment creation uses the session-based CSRF token system.
- **IDOR Protection**: `appointment_confirmation.php` and related views verify request ownership before displaying data.
- **Status Control**: Students cannot set their own appointment status; it defaults to `Scheduled`.

## 6. Files Created
- `config/appointments.php`: Central configuration for hours and capacity.
- `appointments.php`: The scheduling UI.
- `data/get_slots.php`: AJAX endpoint for calculating real-time slot availability.
- `data/save_appointment.php`: Secure backend handler for creating appointments.
- `appointment_confirmation.php`: Success page for scheduled appointments.

## 7. Files Modified
- `my_requests.php`: Added "Schedule" buttons to eligible requests.
- `request_details.php`: Added a section to display current appointment information.

## 8. Legacy Files Preserved
The travel-booking legacy files remain untouched:
- `reserved.php`, `accomodation.php`, `passenger.php`, `payment.php`.

## 9. Known Limitations
- **Holidays**: There is currently no holiday management system. All weekdays are treated as working days.
- **Rescheduling**: Only one active appointment per request is allowed. To reschedule, the existing appointment must be cancelled (implemented by admin in later phases).

## 10. Future Improvements
- **Admin Dashboard**: Implement a calendar view for the Records Office to manage appointments.
- **Notifications**: Trigger an email/SMS when an appointment is scheduled or changed.
- **Payment Lock**: Option to require "Paid" status before allowing appointment scheduling.
