<?php
require_once('../class/Auth.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['valid' => false, 'msg' => 'Invalid request method.']);
    exit();
}

// 1. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid security token. Please refresh the page.']);
    exit();
}

// 2. Collect Input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['valid' => false, 'msg' => 'Please provide both email and password.']);
    exit();
}

// 3. Authenticate
$user = $auth->authenticate($email, $password);

if ($user) {
    // Authentication successful
    // The Auth class already handled session_regenerate_id and session storage

    $url = ($user['role'] === 'admin') ? 'admin/reservation.php' : 'student_area.php';

    echo json_encode(['valid' => true, 'msg' => 'Login successful!', 'url' => $url]);
} else {
    echo json_encode(['valid' => false, 'msg' => 'Invalid email or password.']);
}
?>
