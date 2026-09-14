<?php
require_once('../class/Auth.php');
require_once('../database/Database.php');

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

// 3. Collect and Validate Input
$requestId = $_POST['request_id'] ?? '';
$appDate   = $_POST['appointment_date'] ?? '';
$appTime   = $_POST['appointment_time'] ?? '';
$userId    = $_SESSION['user_id'];

if (empty($requestId) || empty($appDate) || empty($appTime)) {
    echo json_encode(['valid' => false, 'msg' => 'All fields are required.']);
    exit();
}

// 4. Ownership and Eligibility Validation
$sql = "SELECT r.request_id, r.status FROM requests r WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";
$request = $auth->getRow($sql, [$requestId, $userId]);

if (!$request) {
    echo json_encode(['valid' => false, 'msg' => 'Unauthorized request.']);
    exit();
}

$forbiddenStatuses = ['Completed', 'Cancelled', 'Rejected'];
if (in_array($request['status'], $forbiddenStatuses)) {
    echo json_encode(['valid' => false, 'msg' => 'This request cannot be scheduled.']);
    exit();
}

// 5. Prevent Duplicate Active Appointments
$dupSql = "SELECT appointment_id FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
if ($auth->getRow($dupSql, [$requestId])) {
    echo json_encode(['valid' => false, 'msg' => 'An active appointment already exists for this request.']);
    exit();
}

// 6. Server-side Availability Check & Transactional Insert
$config = require('../config/appointments.php');

try {
    // Use a transaction to prevent race conditions/overbooking
    $auth->Begin();

    // Re-verify capacity within the transaction
    $countSql = "SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'";
    $result = $auth->getRow($countSql, [$appDate, $appTime]);
    $booked = $result['count'] ?? 0;

    if ($booked >= $config['capacity']['max_per_slot']) {
        $auth->Commit(); // Close transaction
        echo json_encode(['valid' => false, 'msg' => 'This time slot has just become full. Please choose another.']);
        exit();
    }

    $insertSql = "INSERT INTO appointments (request_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, 'Scheduled')";
    $auth->insertRow($insertSql, [$requestId, $appDate, $appTime]);
    $appId = $auth->lastID();

    $auth->Commit();

    echo json_encode([
        'valid' => true,
        'msg' => 'Appointment scheduled successfully!',
        'url' => 'appointment_confirmation.php?id=' . $appId
    ]);
} catch (Exception $e) {
    $auth->Commit(); // Attempt to close if not already
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
