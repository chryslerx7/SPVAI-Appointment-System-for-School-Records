# Phase 9 Full System Test Plan

This document outlines the comprehensive testing strategy for the SPVAI system.

## Testing Strategy
**Inspect → Test → Identify → Fix → Retest → Verify → Document**

## Test Categories & Cases

### A. Authentication
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Valid Registration | Account created, redirect to login | | | | |
| Missing Required Fields | Error message, form doesn't submit | | | | |
| Invalid Email Format | Error message for invalid email | | | | |
| Duplicate Email | Error: Email already registered | | | | |
| Duplicate Student ID | Error: Student ID already exists | | | | |
| Weak Password | Error: Password too weak | | | | |
| Password Mismatch | Error: Passwords do not match | | | | |
| Invalid Phone Number | Error: Invalid phone format | | | | |
| Valid Login (Student) | Redirect to student_area.php | | | | |
| Valid Login (Admin) | Redirect to admin/dashboard.php | | | | |
| Incorrect Password | Error: Invalid credentials | | | | |
| Nonexistent Account | Error: Invalid credentials | | | | |
| Session Persistence | User stays logged in across pages | | | | |
| Logout | Session destroyed, redirect to login | | | | |
| Protected Page Access (Logged Out) | Redirect to login | | | | |
| Back-button post-logout | No protected content exposed | | | | |

### B. Student Portal
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Dashboard Load | Loads correctly with user info | | | | |
| Summary Metrics | Correct counts of requests/appointments | | | | |
| Recent Activity | Displays most recent requests correctly | | | | |
| Navigation Links | All links in header/footer work | | | | |
| Notification Bell | Shows correct count of unread alerts | | | | |
| Profile View | Displays correct user data | | | | |

### C. Document Requests
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Valid Request Submission | Request created, ref number generated | | | | |
| Missing Purpose | Error: Purpose is required | | | | |
| Invalid Copies (0 or Neg) | Error: Minimum 1 copy required | | | | |
| Excessive Copies | Error: Maximum limit exceeded | | | | |
| Duplicate Submission | Prevents identical request in short time | | | | |
| Request Association | Request linked to the correct student | | | | |
| Initial Status | Status is 'Pending' | | | | |

### D. Request History & Details
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Only Own Requests | Student sees only their requests | | | | |
| Correct Info Display | Document name, date, ref # are correct | | | | |
| Details Link | Redirects to correct request_details.php | | | | |
| IDOR Attempt | Accessing other student's request ID fails | | | | |
| Status Timeline | Correct sequence of status updates | | | | |

### E. Appointments
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Valid Appointment | Scheduled for future date/available slot | | | | |
| Past Date | Error: Cannot schedule in the past | | | | |
| Unavailable Slot | Error: Slot already booked | | | | |
| Duplicate Appointment | Only one active appointment per request | | | | |
| Unauthorized Req ID | Cannot book for a request they don't own | | | | |
| Backend Conflict Check | DB prevents double-booking same slot | | | | |

### F. Payments
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Fee Display | Correct fee shown for document type | | | | |
| Reference Submission | Submitted ref number saved, status 'Pending' | | | | |
| Invalid Ref Number | (If validated) Error for invalid format | | | | |
| Access Control | Cannot see/edit other student's payment | | | | |
| Student Cannot Verify | No way for student to set status to 'Paid' | | | | |

### G. Notifications
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Status Update Trigger | Notification created on status change | | | | |
| Correct Recipient | Notification linked to correct student | | | | |
| Mark as Read | Status changes to read, count decreases | | | | |
| IDOR on Mark-as-read | Cannot mark others' notifications as read | | | | |
| Email Trigger | Email sent (or logged) on key events | | | | |

### H. Admin Portal
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Admin Login | Access granted to admin dashboard | | | | |
| Dashboard Metrics | Counts match actual database records | | | | |
| Request Management | List, Filter, Sort work correctly | | | | |
| Status Update | Status changes and triggers notification | | | | |
| Rejection Reason | Requirement for reason on 'Rejected' | | | | |
| Appointment Management | List and status updates work | | | | |
| Payment Verification | Verification of ref # works | | | | |
| Access Control | Student cannot access admin/ folder | | | | |

### I. Responsive UI (Phase 8 Regression)
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Mobile View (320-430px) | No horizontal overflow, readable text | | | | |
| Tablet View (768px) | Layout adapts, no broken elements | | | | |
| Desktop View (1024px+) | Optimal spacing, layout centered | | | | |
| Table Responsiveness | Data-dense tables handle mobile view | | | | |
| Modal Behavior | Modals center and scale on all screens | | | | |

### J. Security Regression (Phase 7)
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| SQL Injection | Inputs escaped, queries not altered | | | | |
| XSS | HTML tags escaped in output | | | | |
| IDOR (Requests) | Cannot view/edit other student's requests | | | | |
| IDOR (Payments) | Cannot view/edit other student's payments | | | | |
| IDOR (Notifications) | Cannot read/edit other student's alerts | | | | |
| CSRF | POST requests without token rejected | | | | |
| RBAC | admin/ paths protected from students | | | | |

### K. Database Integrity
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Orphaned Records | No requests without users, etc. | | | | |
| Status Consistency | Only allowed statuses in DB | | | | |
| Duplicate Records | No double-booked slots or double-payments | | | | |

### L. Error Handling
| Test Case | Expected Result | Actual | Result | Fix Req | Retest |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Nonexistent ID | 404 or Graceful "Not Found" message | | | | |
| Malformed POST | Handled without PHP warnings/errors | | | | |
| Database Down | Graceful error message (no stack trace) | | | | |
| Invalid Input Types | Handled safely without crashing | | | | |
