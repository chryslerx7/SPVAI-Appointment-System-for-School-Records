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
    echo json_encode(['valid' => false, 'msg' => 'Invalid security token.']);
    exit();
}

// 2. Authentication Check
if (!$auth->isLoggedIn()) {
    echo json_encode(['valid' => false, 'msg' => 'Authentication required.']);
    exit();
}

// 3. Collect Input
$notifId = $_POST['notification_id'] ?? '';

if (empty($notifId)) {
    echo json_encode(['valid' => false, 'msg' => 'Notification ID is required.']);
    exit();
}

// 4. Verify Ownership (IDOR Protection)
$sql = "SELECT notification_id FROM notifications WHERE notification_id = ? AND user_id = ? LIMIT 1";
$notif = $auth->getRow($sql, [$notifId, $_SESSION['user_id']]);

if (!$notif) {
    echo json_encode(['valid' => false, 'msg' => 'Unauthorized access.']);
    exit();
}

// 5. Update to Read
$updateSql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
try {
    $auth->insertRow($updateSql, [$notifId]);
    echo json_encode(['valid' => true, 'msg' => 'Notification marked as read.']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
