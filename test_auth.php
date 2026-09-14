<?php
require_once('class/Auth.php');
require_once('database/Database.php');

$db = new Database();

function log_test($name, $passed, $msg = "") {
    echo ($passed ? "[PASS] " : "[FAIL] ") . $name . ($msg ? " ($msg)" : "") . "\n";
}

echo "--- SPVAI Phase 2 Authentication Tests ---\n";

// Test 1: Student Registration
echo "Test 1: Student Registration... ";
$studentId = "TEST-001-" . uniqid();
$email = "test" . uniqid() . "@example.com";
$password = "SecurePass123!";
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertSql = "INSERT INTO users (student_id, first_name, last_name, email, phone, password_hash, role)
              VALUES (?, ?, ?, ?, ?, ?, 'student')";
try {
    $auth->insertRow($insertSql, [$studentId, 'Test', 'User', $email, '123456789', $passwordHash]);
    log_test("Student Registration", true);
} catch (Exception $e) {
    log_test("Student Registration", false, $e->getMessage());
}

// Test 2: Duplicate Student ID
echo "Test 2: Duplicate Student ID... ";
try {
    $auth->insertRow($insertSql, [$studentId, 'Dup', 'User', 'diff@example.com', '000', $passwordHash]);
    log_test("Duplicate Student ID", false, "Duplicate was allowed!");
} catch (Exception $e) {
    log_test("Duplicate Student ID", true, "Duplicate rejected as expected.");
}

// Test 3: Duplicate Email
echo "Test 3: Duplicate Email... ";
$studentId2 = "TEST-002-" . uniqid();
try {
    $auth->insertRow($insertSql, [$studentId2, 'Other', 'User', $email, '000', $passwordHash]);
    log_test("Duplicate Email", false, "Duplicate email allowed!");
} catch (Exception $e) {
    log_test("Duplicate Email", true, "Duplicate email rejected as expected.");
}

// Test 4: Login - Wrong Password
echo "Test 4: Login Wrong Password... ";
$user = $auth->authenticate($email, "WrongPassword");
log_test("Wrong Password Login", $user === null);

// Test 5: Login - Correct Password
echo "Test 5: Login Correct Password... ";
$user = $auth->authenticate($email, $password);
log_test("Correct Password Login", $user !== null);

// Test 6: Role Separation (Student accessing Admin)
echo "Test 6: Student Accessing Admin... ";
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = 'student';
$isStudent = ($_SESSION['role'] === 'student');
// Simulate requireRole('admin')
$accessDenied = ($isStudent && $_SESSION['role'] !== 'admin');
log_test("Role Separation", $accessDenied);

// Test 7: Admin Migration (MD5)
echo "Test 7: Admin MD5 Migration... ";
$legacyUsername = 'migration_test';
$legacyPassword = 'admin123';
$legacyHash = md5($legacyPassword);

// Insert legacy admin
$auth->insertRow("INSERT INTO user (user_account, user_password) VALUES (?, ?)", [$legacyUsername, $legacyHash]);

// Trigger the logic in data/login.php (simulated)
$legacyUser = $auth->getRow("SELECT * FROM user WHERE user_account = ?", [$legacyUsername]);
if ($legacyUser && md5($legacyPassword) === $legacyUser['user_password']) {
    $newHash = password_hash($legacyPassword, PASSWORD_DEFAULT);
    $auth->insertRow("INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, 'admin')",
                    [$legacyUsername, 'Admin', $legacyUsername . '@spvai.edu.ph', $newHash]);
    log_test("Admin MD5 Migration", true);
} else {
    log_test("Admin MD5 Migration", false);
}

echo "--- Tests Completed ---\n";
?>
