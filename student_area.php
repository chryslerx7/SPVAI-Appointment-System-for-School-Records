<?php
require_once('class/Auth.php');
$auth->requireLogin();
$user = $auth->getCurrentUser();

if (!$user || $user['role'] !== 'student') {
    $auth->requireRole('student', 'login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Student Dashboard - SPVAI Records Office</title>
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
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
            <li><a href="index.php">Home</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h3>
                </div>
                <div class="panel-body">
                    <h4>Your Account Information</h4>
                    <hr>
                    <table class="table table-bordered">
                        <tr>
                            <th>Student ID</th>
                            <td><?= htmlspecialchars($user['student_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <th>Account Role</th>
                            <td><?= ucfirst($user['role']); ?></td>
                        </tr>
                    </table>
                    <div class="alert alert-info">
                        <strong>Note:</strong> The document request system will be available in the next phase.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
