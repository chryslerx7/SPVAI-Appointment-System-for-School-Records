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

    // --- NOTIFICATION TRIGGER ---
    // 1. Fetch request and user details for the notification
    $reqDataSql = "SELECT r.*, u.user_id, dt.document_name FROM requests r
                   JOIN users u ON r.user_id = u.user_id
                   JOIN document_types dt ON r.document_id = dt.document_id
                   WHERE r.request_id = ?";
    $details = $auth->getRow($reqDataSql, [$requestId]);

    if ($details) {
        $year = date('Y', strtotime($details['created_at']));
        $refNum = sprintf("SPVAI-%s-%07d", $year, $requestId);

        $type = '';
        $emailKey = '';
        switch($status) {
            case 'Approved': $type = 'request_approved'; $emailKey = 'request_approved'; break;
            case 'Rejected': $type = 'request_rejected'; $emailKey = 'request_rejected'; break;
            case 'Processing': $type = 'request_processing'; $emailKey = 'request_processing'; break;
            case 'Ready': $type = 'request_ready'; $emailKey = 'request_ready'; break;
            case 'Completed': $type = 'request_completed'; $emailKey = 'request_completed'; break;
            case 'Cancelled': $type = 'request_cancelled'; $emailKey = 'request_cancelled'; break;
        }

        if ($type) {
            $msg = "Your request " . $refNum . " status has been updated to: " . $status . ($status == 'Rejected' ? ". Reason: " . $remarks : ".");

            // Prepare email template data
            $emailData = [
                'ref' => $refNum,
                'doc' => $details['document_name'],
                'remarks' => $remarks
            ];

            // Add appointment info if 'Ready'
            if ($status == 'Ready') {
                $app = $auth->getRow("SELECT appointment_date, appointment_time FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1", [$requestId]);
                if ($app) {
                    $emailData['app'] = date('F j, Y', strtotime($app['appointment_date'])) . ' at ' . date('h:i A', strtotime($app['appointment_time']));
                }
            }

            $template = $notificationService->getTemplate($emailKey, $emailData);
            $notificationService->notifyUser($details['user_id'], $requestId, $type, $msg, $template);
        }
    }

    echo json_encode(['valid' => true, 'msg' => 'Request status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
