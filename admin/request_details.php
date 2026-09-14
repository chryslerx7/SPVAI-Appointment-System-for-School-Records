<?php
require_once('../class/Auth.php');

// Ensure only admins can access
$auth->requireRole('admin');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: requests.php?error=no_request");
    exit();
}

// Fetch complete request details
$sql = "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.student_id, dt.document_name, dt.fee
        FROM requests r
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? LIMIT 1";
$request = $auth->getRow($sql, [$requestId]);

if (!$request) {
    header("Location: requests.php?error=not_found");
    exit();
}

// Fetch associated appointment
$appSql = "SELECT * FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
$appointment = $auth->getRow($appSql, [$requestId]);

// Fetch associated payment
$paySql = "SELECT * FROM payments WHERE request_id = ? ORDER BY created_at DESC LIMIT 1";
$payment = $auth->getRow($paySql, [$requestId]);

// Reference Number
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
    <title>Manage Request - SPVAI Admin</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap-theme.min.css">
</head>
<body style="background-color: #f4f7f6;">

<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">SPVAI Admin</a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li class="active"><a href="requests.php">Requests</a></li>
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
            <h2 class="page-header">Request Details: <?= $refNumber; ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Student Info -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Student Information</strong></div>
                <div class="panel-body">
                    <p><strong>ID:</strong> <?= htmlspecialchars($request['student_id']); ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($request['email']); ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($request['phone'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Payment Status</strong></div>
                <div class="panel-body">
                    <?php if ($payment): ?>
                        <p><strong>Amount:</strong> ₱<?= number_format($payment['amount'], 2); ?></p>
                        <p><strong>Method:</strong> <?= htmlspecialchars($payment['payment_method']); ?></p>
                        <p><strong>Reference:</strong> <?= htmlspecialchars($payment['reference_number']); ?></p>
                        <p><strong>Status:</strong> <span class="label label-info"><?= htmlspecialchars($payment['payment_status']); ?></span></p>
                        <p><strong>Verified By:</strong> <?= htmlspecialchars($payment['verified_by'] ?? 'N/A'); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No payment record found.</p>
                    <?php endif; ?>
                    <a href="payments.php?request_id=<?= $requestId; ?>" class="btn btn-xs btn-default">View All Payments</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Request Details -->
            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Document Request Details</strong></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Document:</strong> <?= htmlspecialchars($request['document_name']); ?></p>
                            <p><strong>Copies:</strong> <?= htmlspecialchars($request['copies']); ?></p>
                            <p><strong>Date Submitted:</strong> <?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="label label-warning"><?= htmlspecialchars($request['status']); ?></span></p>
                            <p><strong>Purpose:</strong><br><em><?= nl2br(htmlspecialchars($request['purpose'])); ?></em></p>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label><strong>Admin Remarks:</strong></label>
                        <textarea class="form-control" id="admin-remarks" rows="3"><?= htmlspecialchars($request['remarks'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Appointment Info -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Appointment</strong></div>
                <div class="panel-body">
                    <?php if ($appointment): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                <p><strong>Time:</strong> <?= date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span class="label label-info"><?= htmlspecialchars($appointment['status']); ?></span></p>
                                <p><strong>Remarks:</strong> <?= htmlspecialchars($appointment['remarks'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No appointment scheduled for this request.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Manage Request Status</strong></div>
                <div class="panel-body">
                    <form id="form-update-status">
                        <input type="hidden" name="request_id" value="<?= $requestId; ?>">
                        <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <label>Update Status:</label>
                                <select name="status" class="form-control">
                                    <option value="Pending" <?= $request['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?= $request['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?= $request['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="Processing" <?= $request['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Ready" <?= $request['status'] == 'Ready' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="Completed" <?= $request['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?= $request['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-block">Update Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.1.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script>
$(document).on('submit', '#form-update-status', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    formData += '&remarks=' + $('#admin-remarks').val();

    var submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).text('Updating...');

    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Update Request');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Update Request');
        }
    });
});
</script>
</body>
</html>
