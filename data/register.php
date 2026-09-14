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

// 2. Collect and Validate Input
$studentId = trim($_POST['student_id'] ?? '');
$firstName  = trim($_POST['first_name'] ?? '');
$lastName   = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$password   = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($studentId) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    echo json_encode(['valid' => false, 'msg' => 'All required fields must be filled.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid email format.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['valid' => false, 'msg' => 'Password must be at least 6 characters long.']);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode(['valid' => false, 'msg' => 'Passwords do not match.']);
    exit();
}

// 3. Check for Duplicates
$checkSql = "SELECT user_id FROM users WHERE student_id = ? OR email = ? LIMIT 1";
$existingUser = $auth->getRow($checkSql, [$studentId, $email]);

if ($existingUser) {
    // Determine which one is duplicated for better error message
    $dupSql = "SELECT student_id, email FROM users WHERE student_id = ? OR email = ? LIMIT 1";
    $dupUser = $auth->getRow($dupSql, [$studentId, $email]);

    if ($dupUser['student_id'] === $studentId) {
        echo json_encode(['valid' => false, 'msg' => 'An account with this Student ID already exists.']);
    } else {
        echo json_encode(['valid' => false, 'msg' => 'An account with this email already exists.']);
    }
    exit();
}

// 4. Secure Password and Insert
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertSql = "INSERT INTO users (student_id, first_name, last_name, email, phone, password_hash, role)
              VALUES (?, ?, ?, ?, ?, ?, 'student')";

try {
    $auth->insertRow($insertSql, [$studentId, $firstName, $lastName, $email, $phone, $passwordHash]);
    echo json_encode(['valid' => true, 'msg' => 'Account created successfully! You can now login.']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'A database error occurred. Please try again.']);
}
?>
