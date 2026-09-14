<?php
require_once('../class/Auth.php');
require_once('../class/NotificationService.php');

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

    // --- NOTIFICATION TRIGGER ---
    $payDataSql = "SELECT p.*, r.request_id, u.user_id, dt.document_name
                   FROM payments p
                   JOIN requests r ON p.request_id = r.request_id
                   JOIN users u ON r.user_id = u.user_id
                   JOIN document_types dt ON r.document_id = dt.document_id
                   WHERE p.payment_id = ?";
    $details = $auth->getRow($payDataSql, [$paymentId]);

    if ($details) {
        $year = date('Y', strtotime($details['created_at']));
        $refNum = sprintf("SPVAI-%s-%07d", $year, $details['request_id']);

        $type = '';
        $emailKey = '';
        switch($status) {
            case 'Paid': $type = 'payment_paid'; $emailKey = 'payment_verified'; break;
            case 'Rejected': $type = 'payment_rejected'; $emailKey = 'payment_rejected'; break;
        }

        if ($type) {
            $msg = "Payment for request " . $refNum . " status updated to: " . $status . ".";
            $emailData = [
                'ref' => $refNum,
                'amount' => number_format($details['amount'], 2),
                'method' => $details['payment_method']
            ];
            $template = $notificationService->getTemplate($emailKey, $emailData);
            $notificationService->notifyUser($details['user_id'], $details['request_id'], $type, $msg, $template);
        }
    }

    echo json_encode(['valid' => true, 'msg' => 'Payment status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
