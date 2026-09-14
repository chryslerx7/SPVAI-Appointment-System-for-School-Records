<?php
require_once('../class/Auth.php');

// Ensure only admins can access
$auth->requireRole('admin');

// Get search and sort parameters
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

// Whitelist sorting
$allowedSort = ['created_at', 'status', 'document_id'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'created_at';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Pagination
$limit = 20;
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $limit;

// Build Query
$sql = "SELECT r.request_id, r.status, r.created_at,
               u.first_name, u.last_name, u.student_id,
               dt.document_name
        FROM requests r
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
if ($search) {
    $sql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.student_id LIKE ? OR dt.document_name LIKE ? OR r.status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY $sortBy $order LIMIT $limit OFFSET $offset";

$requests = $auth->getRows($sql, $params);

// Get total for pagination
$countSql = "SELECT COUNT(*) as total FROM requests r
             JOIN users u ON r.user_id = u.user_id
             JOIN document_types dt ON r.document_id = dt.document_id";
if ($search) {
    $countSql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.student_id LIKE ? OR dt.document_name LIKE ? OR r.status LIKE ?)";
}
$total = $auth->getRow($countSql, $params)['total'] ?? 0;
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Manage Requests - SPVAI Admin</title>
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

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-header">Document Requests</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Search student, ID, doc..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <select name="sort" class="form-control">
                                <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : ''; ?>>Date</option>
                                <option value="status" <?= $sortBy == 'status' ? 'selected' : ''; ?>>Status</option>
                                <option value="document_id" <?= $sortBy == 'document_id' ? 'selected' : ''; ?>>Document</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="order" class="form-control">
                                <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="requests.php" class="btn btn-default">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Ref #</th>
                                    <th>Student</th>
                                    <th>ID</th>
                                    <th>Document</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="7" class="text-center">No requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($requests as $req):
                                        $year = date('Y', strtotime($req['created_at']));
                                        $refNum = sprintf("SPVAI-%s-%07d", $year, $req['request_id']);

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
                                        <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                        <td><?= htmlspecialchars($req['student_id']); ?></td>
                                        <td><?= htmlspecialchars($req['document_name']); ?></td>
                                        <td><span class="label <?= $statusLabel; ?>"><?= htmlspecialchars($req['status']); ?></span></td>
                                        <td><?= date('M d, Y', strtotime($req['created_at'])); ?></td>
                                        <td>
                                            <a href="request_details.php?id=<?= $req['request_id']; ?>" class="btn btn-xs btn-info">View/Manage</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination">
                                <?php for($i=1; $i<=$totalPages; $i++): ?>
                                    <li class="<?= $i == $page ? 'active' : ''; ?>">
                                        <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&sort=<?= $sortBy; ?>&order=<?= $order; ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.1.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
