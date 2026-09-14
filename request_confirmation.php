<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: request_document.php");
    exit();
}

// Fetch request details and validate ownership
$sql = "SELECT r.*, dt.document_name, dt.fee
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";

$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    // Request not found or doesn't belong to user
    header("Location: my_requests.php?error=invalid_request");
    exit();
}

// Generate Reference Number (Consistent with create_request.php)
$year = date('Y');
$refNumber = sprintf("SPVAI-%s-%07d", $year, $requestId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Request Confirmed - SPVAI Records Office</title>
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
                    <h3 class="panel-title">Request Submitted Successfully!</h3>
                </div>
                <div class="panel-body text-center">
                    <div style="font-size: 24px; margin-bottom: 20px;">
                        <strong class="text-success">Reference #: <?= $refNumber; ?></strong>
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th class="text-left">Document</th>
                            <td><?= htmlspecialchars($request['document_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Number of Copies</th>
                            <td><?= htmlspecialchars($request['copies']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Purpose</th>
                            <td><?= htmlspecialchars($request['purpose']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-left">Current Status</th>
                            <td><span class="label label-warning"><?= htmlspecialchars($request['status']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-left">Submission Date</th>
                            <td><?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></td>
                        </tr>
                    </table>

                    <div class="alert alert-info">
                        Your request is now being processed by the Records Office. You can track its status in the <strong>My Requests</strong> section.
                    </div>

                    <a href="my_requests.php" class="btn btn-primary btn-block">View My Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
