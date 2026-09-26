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
$requestId = $_POST['request_id'] ?? '';
$refNum = trim($_POST['reference_number'] ?? '');
$method = $_POST['payment_method'] ?? 'GCash';
$userId = $_SESSION['user_id'];

if (empty($requestId) || empty($refNum)) {
    echo json_encode(['valid' => false, 'msg' => 'Request ID and Reference Number are required.']);
    exit();
}

// 4. Verify Request Ownership
$req = $auth->getRow("SELECT r.*, dt.fee FROM requests r JOIN document_types dt ON r.document_id = dt.document_id WHERE r.request_id = ? AND r.user_id = ?", [$requestId, $userId]);

if (!$req) {
    echo json_encode(['valid' => false, 'msg' => 'Unauthorized access.']);
    exit();
}

// 5. Handle Payment Record
// Check if record already exists
$pay = $auth->getRow("SELECT payment_id, payment_status FROM payments WHERE request_id = ?", [$requestId]);

// P10-007: a Paid payment is final and cannot be resubmitted by the student.
if ($pay && ($pay['payment_status'] ?? '') === 'Paid') {
    echo json_encode(['valid' => false, 'msg' => 'This payment has already been verified and cannot be submitted again.']);
    exit();
}

try {
    if ($pay) {
        // Update existing
        $sql = "UPDATE payments SET reference_number = ?, payment_method = ?, payment_status = 'Pending Verification', payment_date = NOW() WHERE payment_id = ?";
        $auth->insertRow($sql, [$refNum, $method, $pay['payment_id']]);
    } else {
        // Create new
        $sql = "INSERT INTO payments (request_id, amount, payment_method, reference_number, payment_status, payment_date)
                VALUES (?, ?, ?, ?, 'Pending Verification', NOW())";
        $auth->insertRow($sql, [$requestId, $req['fee'], $method, $refNum]);
    }
    echo json_encode(['valid' => true, 'msg' => 'Payment reference submitted successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
