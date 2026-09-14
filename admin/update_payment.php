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
$paymentId = $_POST['payment_id'] ?? '';
$status    = $_POST['payment_status'] ?? '';
$remarks   = trim($_POST['remarks'] ?? '');

if (empty($paymentId) || empty($status)) {
    echo json_encode(['valid' => false, 'msg' => 'Payment ID and Status are required.']);
    exit();
}

// 4. Validate Status (Whitelist)
$allowedStatuses = ['Unpaid', 'Pending Verification', 'Paid', 'Rejected', 'Refunded'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid status value.']);
    exit();
}

// 5. Update Database
// Store the admin who verified it
$adminId = $_SESSION['user_id'];
$verifiedAt = date('Y-m-d H:i:s');

$sql = "UPDATE payments SET payment_status = ?, remarks = ?, verified_by = ?, verified_at = ? WHERE payment_id = ?";

try {
    $auth->insertRow($sql, [$status, $remarks, $adminId, $verifiedAt, $paymentId]);
    echo json_encode(['valid' => true, 'msg' => 'Payment status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
