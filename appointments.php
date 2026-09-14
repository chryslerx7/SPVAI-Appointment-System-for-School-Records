<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: my_requests.php?error=no_request");
    exit();
}

// Validate request ownership and eligibility
$sql = "SELECT r.*, dt.document_name
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";
$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    header("Location: my_requests.php?error=unauthorized");
    exit();
}

// Check if an active appointment already exists
$appSql = "SELECT appointment_id FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
$existingApp = $auth->getRow($appSql, [$requestId]);

if ($existingApp) {
    echo "<script>alert('This request already has an active appointment.'); window.location='my_requests.php';</script>";
    exit();
}

// Check request status eligibility (Cannot schedule if Completed, Cancelled, or Rejected)
$forbiddenStatuses = ['Completed', 'Cancelled', 'Rejected'];
if (in_array($request['status'], $forbiddenStatuses)) {
    echo "<script>alert('This request cannot be scheduled due to its current status: " . htmlspecialchars($request['status']) . "'); window.location='my_requests.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Schedule Appointment - SPVAI Records Office</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-theme.min.css">
</head>
<body style="background-color: lightblue;">

<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">SPVAIRecordsOffice</a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="student_area.php">Dashboard</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Schedule Appointment</h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <strong>Request:</strong> <?= htmlspecialchars($request['document_name']); ?> |
                        <strong>Reference:</strong> SPVAI-<?= date('Y') ?>-<?= sprintf('%07d', $requestId); ?>
                    </div>

                    <form id="form-appointment">
                        <input type="hidden" name="request_id" value="<?= $requestId; ?>">
                        <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app-date">Select Date</label>
                                    <input type="date" name="appointment_date" id="app-date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app-time">Available Time Slots</label>
                                    <select name="appointment_time" id="app-time" class="form-control" required disabled>
                                        <option value="">-- Select Date First --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-center" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-success btn-lg">Confirm Appointment</button>
                            <a href="my_requests.php" class="btn btn-default btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
$(document).on('change', '#app-date', function() {
    var date = $(this).val();
    var timeSelect = $('#app-time');

    if (!date) return;

    timeSelect.prop('disabled', true).html('<option>Loading slots...</option>');

    $.ajax({
        url: 'data/get_slots.php',
        type: 'POST',
        dataType: 'json',
        data: { date: date },
        success: function(data) {
            if (data.valid) {
                var options = '<option value="">-- Choose a Time --</option>';
                data.slots.forEach(function(slot) {
                    var disabled = slot.is_full ? 'disabled' : '';
                    var text = slot.display_time + (slot.is_full ? ' (Fully Booked)' : ' (' + slot.remaining + ' slots left)');
                    options += '<option value="' + slot.time + '" ' + disabled + '>' + text + '</option>';
                });
                timeSelect.html(options).prop('disabled', false);
            } else {
                alert(data.msg);
                timeSelect.html('<option value="">-- Error --</option>');
            }
        },
        error: function() {
            alert('Error fetching available slots.');
            timeSelect.html('<option value="">-- Error --</option>');
        }
    });
});

$(document).on('submit', '#form-appointment', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: 'data/save_appointment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                window.location = data.url;
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Confirm Appointment');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Confirm Appointment');
        }
    });
});
</script>
</body>
</html>
