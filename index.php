<?php
require_once('class/Auth.php');

// Authenticated root router.
// The public landing page lives at public_home.php and never redirects.
// Logged-out visitors go to login.php; logged-in users go to their dashboard.
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = $auth->getCurrentUser();

if (!$user) {
    // Session exists but user record not found (e.g. deleted user)
    $auth->logout();
    header("Location: login.php");
    exit();
}

if ($user['role'] === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: student_area.php");
}
exit();
?>
