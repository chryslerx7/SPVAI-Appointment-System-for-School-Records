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
$requestId = $_POST['request_id'] ?? '';
$status    = $_POST['status'] ?? '';
$remarks   = trim($_POST['remarks'] ?? '');

if (empty($requestId) || empty($status)) {
    echo json_encode(['valid' => false, 'msg' => 'Request ID and Status are required.']);
    exit();
}

// 4. Validate Status (Whitelist)
$allowedStatuses = ['Pending', 'Approved', 'Rejected', 'Processing', 'Ready', 'Completed', 'Cancelled'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid status value.']);
    exit();
}

// 5. Special Validation: Rejections must have remarks
if ($status === 'Rejected' && empty($remarks)) {
    echo json_encode(['valid' => false, 'msg' => 'Please provide a reason for rejecting this request.']);
    exit();
}

// 6. Update Database
$sql = "UPDATE requests SET status = ?, remarks = ? WHERE request_id = ?";

try {
    $auth->insertRow($sql, [$status, $remarks, $requestId]);
    echo json_encode(['valid' => true, 'msg' => 'Request status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
