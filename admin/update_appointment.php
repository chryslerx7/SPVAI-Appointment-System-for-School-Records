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
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['valid' => false, 'msg' => 'Unauthorized access.']);
    exit();
}

// 3. Collect Input
$appId = $_POST['appointment_id'] ?? '';
$status = $_POST['status'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');

if (empty($appId) || empty($status)) {
    echo json_encode(['valid' => false, 'msg' => 'Appointment ID and Status are required.']);
    exit();
}

// 4. Validate Status (Whitelist)
$allowedStatuses = ['Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'No Show'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid status value.']);
    exit();
}

// 5. Update Database
$sql = "UPDATE appointments SET status = ?, remarks = ? WHERE appointment_id = ?";

try {
    $auth->insertRow($sql, [$status, $remarks, $appId]);
    echo json_encode(['valid' => true, 'msg' => 'Appointment status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
