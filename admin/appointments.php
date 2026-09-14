<?php
require_once('../class/Auth.php');

// Ensure only admins can access
$auth->requireRole('admin');

// Sorting and Filtering
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'appointment_date';
$order = $_GET['order'] ?? 'ASC';

$allowedSort = ['appointment_date', 'appointment_time', 'status'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'appointment_date';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Build Query
$sql = "SELECT a.*, r.request_id, u.first_name, u.last_name, dt.document_name
        FROM appointments a
        JOIN requests r ON a.request_id = r.request_id
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
if ($search) {
    $sql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR r.request_id = ? OR a.status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $search, $searchTerm];
}

$sql .= " ORDER BY $sortBy $order";

$appointments = $auth->getRows($sql, $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Manage Appointments - SPVAI Admin</title>
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
            <li class="active"><a href="appointments.php">Appointments</a></li>
            <li><a href="payments.php">Payments</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="../logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-header">Appointment Management</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Search student, request, status..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <select name="sort" class="form-control">
                                <option value="appointment_date" <?= $sortBy == 'appointment_date' ? 'selected' : ''; ?>>Date</option>
                                <option value="appointment_time" <?= $sortBy == 'appointment_time' ? 'selected' : ''; ?>>Time</option>
                                <option value="status" <?= $sortBy == 'status' ? 'selected' : ''; ?>>Status</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="order" class="form-control">
                                <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="appointments.php" class="btn btn-default">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr class="active">
                                    <th>Student</th>
                                    <th>Ref #</th>
                                    <th>Document</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr><td colspan="7" class="text-center">No appointments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($appointments as $app):
                                        $year = date('Y', strtotime($app['created_at']));
                                        $refNum = sprintf("SPVAI-%s-%07d", $year, $app['request_id']);

                                        $statusLabel = 'label-default';
                                        switch($app['status']) {
                                            case 'Scheduled': $statusLabel = 'label-warning'; break;
                                            case 'Confirmed': $statusLabel = 'label-info'; break;
                                            case 'Completed': $statusLabel = 'label-success'; break;
                                            case 'Cancelled': $statusLabel = 'label-default'; break;
                                            case 'No Show': $statusLabel = 'label-danger'; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                        <td><strong><?= $refNum; ?></strong></td>
                                        <td><?= htmlspecialchars($app['document_name']); ?></td>
                                        <td><?= date('M d, Y', strtotime($app['appointment_date'])); ?></td>
                                        <td><?= date('h:i A', strtotime($app['appointment_time'])); ?></td>
                                        <td><span class="label <?= $statusLabel; ?>"><?= htmlspecialchars($app['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-xs btn-info btn-manage-app"
                                                    data-id="<?= $app['appointment_id']; ?>"
                                                    data-status="<?= $app['status']; ?>">
                                                Manage
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

<!-- Manage Appointment Modal -->
<div class="modal fade" id="modal-manage-app" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Update Appointment Status</h4>
            </div>
            <form id="form-update-app">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="input-app-id">
                    <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                    <div class="form-group">
                        <label>New Status</label>
                        <select name="status" id="input-app-status" class="form-control">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="No Show">No Show</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.1.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script>
$(document).on('click', '.btn-manage-app', function() {
    var appId = $(this).data('id');
    var status = $(this).data('status');

    $('#input-app-id').val(appId);
    $('#input-app-status').val(status);
    $('#modal-manage-app').modal('show');
});

$(document).on('submit', '#form-update-app', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Updating...');

    $.ajax({
        url: 'update_appointment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Update Status');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Update Status');
        }
    });
});
</script>
</body>
</html>
