<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

// Fetch notifications for current user
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$notifications = $auth->getRows($sql, [$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>My Notifications - SPVAI Records Office</title>
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
            <li><a href="my_requests.php">My Requests</a></li>
            <li class="active"><a href="notifications.php">Notifications</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Notifications</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($notifications)): ?>
                        <div class="alert alert-info text-center">
                            You have no notifications at this time.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach($notifications as $notif): ?>
                                <div class="list-group-item <?= $notif['is_read'] ? '' : 'list-group-item-warning'; ?>" id="notif-<?= $notif['notification_id']; ?>">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $notif['type']))); ?></strong>
                                            <p class="small"><?= htmlspecialchars($notif['message']); ?></p>
                                            <span class="text-muted" style="font-size: 11px;"><?= date('M d, Y h:i A', strtotime($notif['created_at'])); ?></span>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <?php if (!$notif['is_read']): ?>
                                                <button class="btn btn-xs btn-default btn-mark-read" data-id="<?= $notif['notification_id']; ?>">Mark as Read</button>
                                            <?php endif; ?>
                                            <?php if ($notif['request_id']): ?>
                                                <a href="request_details.php?id=<?= $notif['request_id']; ?>" class="btn btn-xs btn-info">View Request</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
$(document).on('click', '.btn-mark-read', function() {
    var notifId = $(this).data('id');
    var btn = $(this);

    $.ajax({
        url: 'data/mark_read.php',
        type: 'POST',
        dataType: 'json',
        data: {
            notification_id: notifId,
            csrf_token: '<?= $auth->generateCsrfToken(); ?>'
        },
        success: function(data) {
            if (data.valid) {
                btn.fadeOut();
                $('#notif-' + notifId).removeClass('list-group-item-warning');
            } else {
                alert(data.msg);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
});
</script>
</body>
</html>
