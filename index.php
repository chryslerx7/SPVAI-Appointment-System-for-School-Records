<?php
require_once('class/Auth.php');

// Determine routing based on authentication status
if ($auth->isLoggedIn()) {
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
} else {
    // Not logged in, send to modern login page
    header("Location: login.php");
    exit();
}
?>
