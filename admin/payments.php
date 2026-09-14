<?php
require_once('../class/Auth.php');

// Ensure only admins can access
$auth->requireRole('admin');

// Sorting and Filtering
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

$allowedSort = ['created_at', 'payment_status', 'amount'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'created_at';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Build Query
$sql = "SELECT p.*, u.first_name, u.last_name, r.request_id, dt.document_name
        FROM payments p
        JOIN requests r ON p.request_id = r.request_id
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
if ($search) {
    $sql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR p.reference_number LIKE ? OR p.payment_status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY $sortBy $order";

$payments = $auth->getRows($sql, $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Manage Payments - SPVAI Admin</title>
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
            <li><a href="requests.php">Requests</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li class="active"><a href="payments.php">Payments</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="../logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-header">Payment Verification</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Search reference, student, status..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <select name="sort" class="form-control">
                                <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : ''; ?>>Date</option>
                                <option value="payment_status" <?= $sortBy == 'payment_status' ? 'selected' : ''; ?>>Status</option>
                                <option value="amount" <?= $sortBy == 'amount' ? 'selected' : ''; ?>>Amount</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="order" class="form-control">
                                <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="payments.php" class="btn btn-default">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="active">
                                <tr>
                                    <th>Student</th>
                                    <th>Ref #</th>
                                    <th>Doc</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>,
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                    <tr><td colspan="8" class="text-center">No payment records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($payments as $pay):
                                        $statusLabel = 'label-default';
                                        switch($pay['payment_status']) {
                                            case 'Paid': $statusLabel = 'label-success'; break;
                                            case 'Pending Verification': $statusLabel = 'label-warning'; break;
                                            case 'Rejected': $statusLabel = 'label-danger'; break;
                                            case 'Refunded': $statusLabel = 'label-info'; break;
                                            case 'Unpaid': $statusLabel = 'label-default'; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pay['first_name'] . ' ' . $pay['last_name']); ?></td>
                                        <td><strong><?= htmlspecialchars($pay['reference_number'] ?? 'N/A'); ?></strong></td>
                                        <td><?= htmlspecialchars($pay['document_name']); ?></td>
                                        <td>₱<?= number_format($pay['amount'], 2); ?></td>
                                        <td><?= htmlspecialchars($pay['payment_method']); ?></td>
                                        <td><span class="label <?= $statusLabel; ?>"><?= htmlspecialchars($pay['payment_status']); ?></span></td>
                                        <td><?= date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-xs btn-info btn-verify-pay"
                                                    data-id="<?= $pay['payment_id']; ?>"
                                                    data-status="<?= $pay['payment_status']; ?>">
                                                Verify
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verify Payment Modal -->
<div class="modal fade" id="modal-verify-pay" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Verify Payment</h4>
            </div>
            <form id="form-verify-pay">
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="input-pay-id">
                    <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                    <div class="form-group">
                        <label>Update Status</label>
                        <select name="payment_status" id="input-pay-status" class="form-control">
                            <option value="Unpaid">Unpaid</option>
                            <option value="Pending Verification">Pending Verification</option>
                            <option value="Paid">Paid</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Verification Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.1.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script>
$(document).on('click', '.btn-verify-pay', function() {
    var payId = $(this).data('id');
    var status = $(this).data('status');

    $('#input-pay-id').val(payId);
    $('#input-pay-status').val(status);
    $('#modal-verify-pay').modal('show');
});

$(document).on('submit', '#form-verify-pay', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Verifying...');

    $.ajax({
        url: 'update_payment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Save Verification');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Save Verification');
        }
    });
});
</script>
</body>
</html>
