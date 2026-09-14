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

    // --- NOTIFICATION TRIGGER ---
    $appDataSql = "SELECT a.*, r.request_id, u.user_id, dt.document_name
                   FROM appointments a
                   JOIN requests r ON a.request_id = r.request_id
                   JOIN users u ON r.user_id = u.user_id
                   JOIN document_types dt ON r.document_id = dt.document_id
                   WHERE a.appointment_id = ?";
    $details = $auth->getRow($appDataSql, [$appId]);

    if ($details) {
        $year = date('Y', strtotime($details['created_at']));
        $refNum = sprintf("SPVAI-%s-%07d", $year, $details['request_id']);

        $type = '';
        $emailKey = '';
        switch($status) {
            case 'Confirmed': $type = 'appointment_confirmed'; $emailKey = 'appointment_confirmed'; break;
            case 'Cancelled': $type = 'appointment_cancelled'; $emailKey = 'appointment_cancelled'; break;
        }

        if ($type) {
            $msg = "Your appointment for request " . $refNum . " has been updated to: " . $status . ".";
            $emailData = [
                'ref' => $refNum,
                'doc' => $details['document_name'],
                'date' => date('F j, Y', strtotime($details['appointment_date'])),
                'time' => date('h:i A', strtotime($details['appointment_time']))
            ];
            $template = $notificationService->getTemplate($emailKey, $emailData);
            $notificationService->notifyUser($details['user_id'], $details['request_id'], $type, $msg, $template);
        }
    }

    echo json_encode(['valid' => true, 'msg' => 'Appointment status updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
