<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

// Fetch requests for current user
$sql = "SELECT r.request_id, r.status, r.created_at, dt.document_name
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC";

$myRequests = $auth->getRows($sql, [$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>My Requests - SPVAI Records Office</title>
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
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">My Document Requests</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($myRequests)): ?>
                        <div class="alert alert-info text-center">
                            You haven't submitted any requests yet.
                            <br><br>
                            <a href="request_document.php" class="btn btn-primary">Request Your First Document</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Reference #</th>
                                        <th>Document</th>
                                        <th>Date Submitted</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($myRequests as $req):
                                        $year = date('Y', strtotime($req['created_at']));
                                        $refNum = sprintf("SPVAI-%s-%07d", $year, $req['request_id']);

                                        // Status Label Color
                                        $statusLabel = 'label-default';
                                        switch($req['status']) {
                                            case 'Pending': $statusLabel = 'label-warning'; break;
                                            case 'Approved': $statusLabel = 'label-info'; break;
                                            case 'Processing': $statusLabel = 'label-primary'; break;
                                            case 'Ready': $statusLabel = 'label-success'; break;
                                            case 'Completed': $statusLabel = 'label-success'; break;
                                            case 'Rejected': $statusLabel = 'label-danger'; break;
                                            case 'Cancelled': $statusLabel = 'label-default'; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><strong><?= $refNum; ?></strong></td>
                                        <td><?= htmlspecialchars($req['document_name']); ?></td>
                                        <td><?= date('M d, Y', strtotime($req['created_at'])); ?></td>
                                        <td><span class="label <?= $statusLabel; ?>"><?= htmlspecialchars($req['status']); ?></span></td>
                                        <td>
                                            <a href="request_details.php?id=<?= $req['request_id']; ?>" class="btn btn-xs btn-info">Details</a>
                                            <?php
                                                // Show "Schedule" button if status is appropriate and no active appointment exists
                                                $appSql = "SELECT appointment_id FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
                                                $hasApp = $auth->getRow($appSql, [$req['request_id']]);
                                                $allowedStatuses = ['Pending', 'Approved', 'Processing', 'Ready'];
                                                if (!$hasApp && in_array($req['status'], $allowedStatuses)) {
                                                    echo '<a href="appointments.php?id=' . $req['request_id'] . '" class="btn btn-xs btn-success">Schedule</a>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
