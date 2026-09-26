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

// 2. Authenticated student access (ownership comes from the session only)
if (!$auth->isLoggedIn() || (($_SESSION['role'] ?? '') !== 'student')) {
    echo json_encode(['valid' => false, 'msg' => 'Authentication required.']);
    exit();
}

$userId = $_SESSION['user_id'];

// 3. Collect explicitly whitelisted fields only.
// user_id, student_id, role, password_hash and timestamps are never accepted.
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phoneRaw  = trim($_POST['phone'] ?? '');

// 4. Validate input (schema-bound limits only)
if ($firstName === '' || $lastName === '' || $email === '') {
    echo json_encode(['valid' => false, 'msg' => 'First name, last name, and email are required.']);
    exit();
}

if (strlen($firstName) > 100 || strlen($lastName) > 100) {
    echo json_encode(['valid' => false, 'msg' => 'First and last names must not exceed 100 characters.']);
    exit();
}

if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['valid' => false, 'msg' => 'Please enter a valid email address.']);
    exit();
}

if (strlen($phoneRaw) > 20) {
    echo json_encode(['valid' => false, 'msg' => 'Phone number must not exceed 20 characters.']);
    exit();
}

// Phone is optional: store NULL when empty (column is nullable).
$phone = ($phoneRaw === '') ? null : $phoneRaw;

// 5. Email uniqueness (exclude the current user)
$dupSql = "SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1";
if ($auth->getRow($dupSql, [$email, $userId])) {
    echo json_encode(['valid' => false, 'msg' => 'This email address is already in use by another account.']);
    exit();
}

// 6. Scoped update of whitelisted fields only
$updateSql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE user_id = ?";

try {
    $auth->insertRow($updateSql, [$firstName, $lastName, $email, $phone, $userId]);

    echo json_encode([
        'valid' => true,
        'msg' => 'Profile updated successfully!',
        'first_name' => $firstName,
        'last_name' => $lastName
    ]);
} catch (Exception $e) {
    // Handle UNIQUE race conditions without leaking SQL details
    if (stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), '1062') !== false) {
        echo json_encode(['valid' => false, 'msg' => 'This email address is already in use by another account.']);
    } else {
        echo json_encode(['valid' => false, 'msg' => 'A database error occurred. Please try again.']);
    }
}
?>
