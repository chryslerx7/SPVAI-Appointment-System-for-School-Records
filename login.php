<?php
require_once('class/Auth.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Login - SPVAI Records Office</title>
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
            <li><a href="register.php">Register</a></li>
            <li><a href="index.php">Home</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Account Login</h3>
                </div>
                <div class="panel-body">
                    <form id="form-login" class="form-horizontal">
                        <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Email Address</label>
                            <div class="col-sm-8">
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Password</label>
                            <div class="col-sm-8">
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
$(document).on('submit', '#form-login', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
        url: 'data/auth_login.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                window.location = data.url;
            } else {
                alert(data.msg);
            }
        },
        error: function() {
            alert('An error occurred during login. Please try again.');
        }
    });
});
</script>
</body>
</html>
