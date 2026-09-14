# PHASE 1: DATABASE & FOUNDATION DOCUMENTATION

This document describes the database changes implemented in Phase 1 of the SPVAI project transformation.

## 1. New Tables Created

The following tables were added to establish a request-centric architecture:

### `users`
**Purpose**: Centralized account management for students and administrators.
- **Key Fields**: `user_id`, `student_id` (Unique), `email` (Unique), `password_hash`, `role` (student/admin).
- **Design Note**: Replaces the legacy `user` table for new functionality. Uses `password_hash` for secure storage.

### `document_types`
**Purpose**: Catalog of documents available for request.
- **Key Fields**: `document_id`, `document_name`, `fee` (DECIMAL 10,2), `processing_days`.
- **Seed Data**: SF10, COE, COG, EMOI.

### `requests`
**Purpose**: The core entity linking a user to a requested document.
- **Key Fields**: `request_id`, `user_id`, `document_id`, `status`, `copies`.
- **Statuses**: Pending, Approved, Rejected, Processing, Ready, Completed, Cancelled.

### `appointments`
**Purpose**: Tracking the date and time for document pickup or processing.
- **Key Fields**: `appointment_id`, `request_id`, `appointment_date`, `appointment_time`, `status`.
- **Statuses**: Scheduled, Confirmed, Completed, Cancelled, No Show.

### `payments`
**Purpose**: Tracking payments for specific requests.
- **Key Fields**: `payment_id`, `request_id`, `amount`, `payment_method`, `payment_status`, `verified_by`.
- **Statuses**: Unpaid, Pending Verification, Paid, Rejected, Refunded.
- **Verification**: Supports administrator verification via `verified_by` and `verified_at`.

### `notifications`
**Purpose**: Foundation for user alerts.
- **Key Fields**: `notification_id`, `user_id`, `request_id`, `type`, `message`, `is_read`.

---

## 2. Database Relationships

The new schema follows a request-centric model:

- **One-to-Many**: `users` $\rightarrow$ `requests` (A user can make multiple requests).
- **One-to-Many**: `document_types` $\rightarrow$ `requests` (A document type can be requested multiple times).
- **One-to-One/Many**: `requests` $\rightarrow$ `appointments` (Usually one appointment per request).
- **One-to-Many**: `requests` $\rightarrow$ `payments` (Allows for partial payments or payment attempts).
- **One-to-Many**: `users` $\rightarrow$ `notifications` (User receives multiple alerts).
- **One-to-Many**: `requests` $\rightarrow$ `notifications` (A request can trigger multiple status updates).

---

## 3. Payment Tracking Design

Payment is strictly decoupled from the document type and associated with the **Request**.

**Workflow**:
1. Student creates a **Request** $\rightarrow$ System creates a **Payment** record with status `Unpaid`.
2. Student provides payment $\rightarrow$ Admin updates **Payment** to `Pending Verification`.
3. Admin verifies funds $\rightarrow$ Admin updates **Payment** to `Paid` and sets `verified_by`.

**Security**: No sensitive banking or card data is stored. Only reference numbers and verification logs.

---

## 4. Legacy Tables

The following legacy tables were preserved for backward compatibility:
- `user`, `accomodation`, `booked`, `destination`, `origin`, `transaction`, `status`.

**Plan for removal**: These tables will be removed in a later phase once the User and Admin interfaces are fully migrated to the new `users` and `requests` tables.

---

## 5. Setup Instructions

To apply the Phase 1 database schema:

1. Ensure your XAMPP/Apache/MySQL services are running.
2. Navigate to: `http://localhost/SPVAI/database/setup_phase1.php` in your browser.
3. The script will execute `database/phase1_schema.sql` and report the results.
4. Once complete, the new tables will be available in the `spvaii` database.
