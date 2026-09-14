<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

$appId = $_GET['id'] ?? null;

if (!$appId) {
    header("Location: my_requests.php");
    exit();
}

// Fetch appointment and request details, verify ownership
$sql = "SELECT a.*, r.request_id, dt.document_name
        FROM appointments a
        JOIN requests r ON a.request_id = r.request_id
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE a.appointment_id = ? AND r.user_id = ? LIMIT 1";

$app = $auth->getRow($sql, [$appId, $_SESSION['user_id']]);

if (!$app) {
    header("Location: my_requests.php?error=unauthorized");
    exit();
}

// Reference Number
$year = date('Y', strtotime($app['created_at']));
$refNumber = sprintf("SPVAI-%s-%07d", $year, $app['request_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Appointment Confirmed - SPVAI Records Office</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-theme.min.css">
</head>
<body style="background-color: lightblue;">

<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">SPVAIRecordsOffice</a>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="student_area.php">Dashboard</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-success">
                <div class="panel-heading text-center">
                    <h3 class="panel-title">Appointment Scheduled Successfully!</h3>
                </div>
                <div class="panel-body text-center">
                    <div style="font-size: 24px; margin-bottom: 20px;">
                        <strong class="text-success">Scheduled</strong>
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th class="text-left">Request Ref #</th>
                            <td><?= $refNumber; ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Document</th>
                            <td><?= htmlspecialchars($app['document_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Appointment Date</th>
                            <td><?= date('F j, Y', strtotime($app['appointment_date'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Appointment Time</th>
                            <td><?= date('h:i A', strtotime($app['appointment_time'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Status</th>
                            <td><span class="label label-warning"><?= htmlspecialchars($app['status']); ?></span></td>
                        </tr>
                    </table>

                    <div class="alert alert-info">
                        Please arrive 15 minutes before your scheduled time.
                    </div>

                    <a href="my_requests.php" class="btn btn-primary btn-block">Back to My Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
