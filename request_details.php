<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: my_requests.php");
    exit();
}

// Fetch request details and strictly validate ownership (IDOR Protection)
$sql = "SELECT r.*, dt.document_name, dt.fee
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";

$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    // Request not found or doesn't belong to user
    header("Location: my_requests.php?error=unauthorized");
    exit();
}

// Generate Reference Number
$year = date('Y', strtotime($request['created_at']));
$refNumber = sprintf("SPVAI-%s-%07d", $year, $requestId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Request Details - SPVAI Records Office</title>
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
            <li><a href="request_document.php">Request Document</a></li>
            <li class="active"><a href="my_requests.php">My Requests</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Request Details: <?= $refNumber; ?></h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="info">Document</th>
                            <td><strong><?= htmlspecialchars($request['document_name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="info">Reference Number</th>
                            <td><?= $refNumber; ?></td>
                        </tr>
                        <tr>
                            <th class="info">Purpose</th>
                            <td><?= nl2br(htmlspecialchars($request['purpose'])); ?></td>
                        </tr>
                        <tr>
                            <th class="info">Number of Copies</th>
                            <td><?= htmlspecialchars($request['copies']); ?></td>
                        </tr>
                        <tr>
                            <th class="info">Submission Date</th>
                            <td><?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th class="info">Current Status</th>
                            <td>
                                <?php
                                    $status = $request['status'];
                                    $label = 'label-default';
                                    switch($status) {
                                        case 'Pending': $label = 'label-warning'; break;
                                        case 'Approved': $label = 'label-info'; break;
                                        case 'Processing': $label = 'label-primary'; break;
                                        case 'Ready': $label = 'label-success'; break;
                                        case 'Completed': $label = 'label-success'; break;
                                        case 'Rejected': $label = 'label-danger'; break;
                                        case 'Cancelled': $label = 'label-default'; break;
                                    }
                                ?>
                                <span class="label <?= $label; ?>"><?= htmlspecialchars($status); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="info">Admin Remarks</th>
                            <td><?= !empty($request['remarks']) ? htmlspecialchars($request['remarks']) : 'No remarks yet.'; ?></td>
                        </tr>
                    </table>

                    <div class="text-center">
                        <a href="my_requests.php" class="btn btn-default">Back to My Requests</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .info { width: 40%; background-color: #f9f9f9; }
</style>
<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
