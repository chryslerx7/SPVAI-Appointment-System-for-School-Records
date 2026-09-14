<?php
require_once('../class/Auth.php');
require_once('../database/Database.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['valid' => false, 'msg' => 'Invalid request method.']);
    exit();
}

$date = $_POST['date'] ?? '';
if (empty($date)) {
    echo json_encode(['valid' => false, 'msg' => 'Date is required.']);
    exit();
}

// Load config
$config = require('../config/appointments.php');

// 1. Validate Date (not in past, not weekend)
$timestamp = strtotime($date);
$today = strtotime(date('Y-m-d'));
$dayOfWeek = date('N', $timestamp);

if ($timestamp < $today) {
    echo json_encode(['valid' => false, 'msg' => 'Cannot schedule appointments in the past.']);
    exit();
}

if (!in_array($dayOfWeek, $config['scheduling_rules']['allowed_days'])) {
    echo json_encode(['valid' => false, 'msg' => 'The Records Office is closed on this day.']);
    exit();
}

// 2. Generate Time Slots
$slots = [];
$current = strtotime($config['office_hours']['open']);
$close = strtotime($config['office_hours']['close']);
$duration = $config['office_hours']['slot_duration'] * 60;

while ($current < $close) {
    $time = date('H:i:s', $current);
    $slots[] = $time;
    $current += $duration;
}

// 3. Check Current Bookings for this date
$sql = "SELECT appointment_time, COUNT(*) as count
        FROM appointments
        WHERE appointment_date = ? AND status != 'Cancelled'
        GROUP BY appointment_time";
$bookedSlots = $auth->getRows($sql, [$date]);

$bookedMap = [];
foreach ($bookedSlots as $bs) {
    $bookedMap[$bs['appointment_time']] = (int)$bs['count'];
}

// 4. Calculate Availability
$availability = [];
foreach ($slots as $slot) {
    $booked = $bookedMap[$slot] ?? 0;
    $remaining = $config['capacity']['max_per_slot'] - $booked;

    $availability[] = [
        'time' => $slot,
        'display_time' => date('h:i A', strtotime($slot)),
        'remaining' => $remaining,
        'is_full' => ($remaining <= 0)
    ];
}

echo json_encode(['valid' => true, 'slots' => $availability]);
?>
