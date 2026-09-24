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

// 3. Try Legacy User Table (Username/MD5)
// Tables: user (user_id, user_account, user_password)
$legacySql = "SELECT * FROM user WHERE user_account = ? LIMIT 1";
$legacyUser = $auth->getRow($legacySql, [$username]);

if ($legacyUser && md5($password) === $legacyUser['user_password']) {
    // Legacy authentication successful! Migrate to new users table.

    $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);

    // We don't have full details for legacy users (first_name, last_name, email).
    // We'll use the account name as the email placeholder and name.
    $email = $username . '@spvai.edu.ph';

    $insertSql = "INSERT INTO users (first_name, last_name, email, password_hash, role)
                  VALUES (?, ?, ?, ?, 'admin')";

    try {
        $auth->insertRow($insertSql, [$username, 'Admin', $email, $newPasswordHash]);
        $newUserId = $auth->lastID();

        // Optional: Delete from legacy table to prevent future MD5 use
        $auth->deleteRow("DELETE FROM user WHERE user_id = ?", [$legacyUser['user_id']]);

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['role'] = 'admin';

        echo json_encode(['valid' => true, 'msg' => 'Admin Login successful (Migrated)!', 'url' => 'dashboard.php']);
    } catch (Exception $e) {
        // If migration fails, we still let them in but log the error
        $_SESSION['user_id'] = $legacyUser['user_id'];
        $_SESSION['role'] = 'admin';
        echo json_encode(['valid' => true, 'msg' => 'Admin Login successful!', 'url' => 'dashboard.php']);
    }
    exit();
}

echo json_encode(['valid' => false, 'msg' => 'Invalid Username / Password!']);
?>
