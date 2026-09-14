<?php
require_once('../class/Auth.php');

// Ensure only admins can access
$auth->requireRole('admin');

// 1. Fetch Statistics
// Pending Requests
$pendingReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Pending'");

// Approved Requests
$approvedReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Approved'");

// Rejected Requests
$rejectedReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Rejected'");

// Scheduled Appointments
$scheduledApps = $auth->getRow("SELECT COUNT(*) as count FROM appointments WHERE status = 'Scheduled'");

// Today's Appointments
$today = date('Y-m-d');
$todayApps = $auth->getRow("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND status != 'Cancelled'", [$today]);

// Pending Payments
$pendingPayments = $auth->getRow("SELECT COUNT(*) as count FROM payments WHERE payment_status = 'Pending Verification'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Admin Dashboard - SPVAI Records Office</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap-theme.min.css">
    <style>
        .stat-card {
            padding: 20px;
            border-radius: 5px;
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-count {
            font-size: 32px;
            font-weight: bold;
            display: block;
        }
        .stat-label {
            font-size: 14px;
            text-transform: uppercase;
        }
    </style>
</head>
<body style="background-color: #f4f7f6;">

<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">SPVAI Admin</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="requests.php">Requests</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="payments.php">Payments</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="../logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-header">Records Office Overview</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card" style="background-color: #f0ad4e;">
                <span class="stat-count"><?= $pendingReqs['count'] ?? 0; ?></span>
                <span class="stat-label">Pending Requests</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color: #5bc0de;">
                <span class="stat-count"><?= $approvedReqs['count'] ?? 0; ?></span>
                <span class="stat-label">Approved Requests</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color: #d9534f;">
                <span class="stat-count"><?= $rejectedReqs['count'] ?? 0; ?></span>
                <span class="stat-label">Rejected Requests</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color: #5cb85c;">
                <span class="stat-count"><?= $scheduledApps['count'] ?? 0; ?></span>
                <span class="stat-label">Total Scheduled</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Today's Appointments</h3>
                </div>
                <div class="panel-body text-center">
                    <span style="font-size: 48px; font-weight: bold;"><?= $todayApps['count'] ?? 0; ?></span>
                    <p class="text-muted">Appointments scheduled for today</p>
                    <a href="appointments.php" class="btn btn-primary">View All Appointments</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Payment Verification</h3>
                </div>
                <div class="panel-body text-center">
                    <span style="font-size: 48px; font-weight: bold; color: #d9534f;"><?= $pendingPayments['count'] ?? 0; ?></span>
                    <p class="text-muted">Payments awaiting verification</p>
                    <a href="payments.php" class="btn btn-danger">Verify Payments</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.1.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
