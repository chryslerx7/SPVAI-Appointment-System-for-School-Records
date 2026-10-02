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

$username = trim($_POST['un'] ?? '');
$password = $_POST['pwd'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['valid' => false, 'msg' => 'Please provide both username/email and password.']);
    exit();
}

// 2. Try New Users Table (Email/Password)
$user = $auth->authenticate($username, $password);

if ($user) {
    if ($user['role'] === 'admin') {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['valid' => true, 'msg' => 'Admin Login successful!', 'url' => 'dashboard.php']);
    } else {
        echo json_encode(['valid' => false, 'msg' => 'Access denied. Administrator account required.']);
    }
    exit();
}

echo json_encode(['valid' => false, 'msg' => 'Invalid Username / Password!']);
?>
