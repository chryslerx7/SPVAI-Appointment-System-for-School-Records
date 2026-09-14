
## [P9-001]
Description: Improper transaction handling in save_appointment.php and missing Rollback method in Database class.
Severity: HIGH
Steps to reproduce:
1. Attempt to schedule an appointment that triggers a database exception (e.g. during the insert).
2. Observe that the catch block calls Commit() instead of Rollback().
Expected: Transaction should be rolled back on error to ensure data integrity.
Actual: Transaction is committed (if not already failed) and no Rollback method exists in the Database wrapper.
Root cause: Logic error in save_appointment.php and incomplete implementation of transaction methods in Database.php.
Fix: Added Rollback() to Database class and updated save_appointment.php to call it in the catch block.
Retest: Pending.
Status: OPEN

## [P9-002]
Description: Syntax error in NotificationService::getTemplate.
Severity: MEDIUM
Steps to reproduce:
1. Trigger a notification that uses a template not found in the templates array.
2. Observe PHP Parse Error.
Expected: Fallback template should be returned.
Actual: Syntax error due to missing quote in associative array.
Root cause: Typo in the fallback return value.
Fix: Corrected the array syntax in getTemplate().
Retest: Pending.
Status: OPEN

## [P9-003]
Description: Root URL incorrectly routes to legacy reserved.php (via legacy landing page).
Severity: HIGH
Steps to reproduce:
1. Open http://localhost/SPVAI/ in a browser.
2. Observe the legacy landing page.
3. Click "Reserved Now" or observe that the entry point is not the modern portal.
Expected: Root URL opens the modern SPVAI entry flow (Login/Dashboard).
Actual: Root URL opens the legacy landing page leading to reserved.php.
Root cause: index.php contains the legacy Bootstrap landing page instead of a modern routing script.
Fix: Replaced index.php with a PHP router that redirects based on authentication status (Not logged in -> login.php, Student -> student_area.php, Admin -> admin/dashboard.php).
Retest: Pending.
Status: OPEN

## [P9-004]
Description: Admin layout include path error.
Severity: HIGH
Steps to reproduce:
1. Navigate to http://localhost/SPVAI/admin/dashboard.php.
2. Observe Fatal error: Failed opening required 'layouts/admin_header.php'.
Expected: Admin pages load correctly with the admin header and footer.
Actual: PHP cannot find the layouts directory because it is resolving relative to the admin/ directory.
Root cause: The include paths were specified as 'layouts/admin_header.php' instead of using a path relative to the project root.
Fix: Updated all admin layout includes to use __DIR__ . '/../layouts/admin_header.php' and __DIR__ . '/../layouts/admin_footer.php'.
Retest: Pending.
Status: OPEN

## [P9-005]
Description: Layout Dependency Include Path Error.
Severity: HIGH
Steps to reproduce:
1. Access any page that includes student_header.php or admin_header.php.
2. Observe Fatal error: Failed opening required 'class/Auth.php'.
Expected: Layouts should correctly include the Auth class from the project root.
Actual: Include paths are relative to the layouts/ directory, making them fail.
Root cause: layouts/admin_header.php and layouts/student_header.php used 'class/Auth.php' instead of a path relative to the filesystem root.
Fix: Updated both headers to use __DIR__ . '/../class/Auth.php'.
Retest: Pending.
Status: OPEN
