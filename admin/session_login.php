<?php
require_once('session_login.php'); // Keep for legacy
require_once('../class/Auth.php');

// Ensure the user is logged in and is an administrator
$auth->requireRole('admin', 'index.php');
?>
