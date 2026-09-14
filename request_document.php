<?php
require_once('class/Auth.php');

// Require student login
$auth->requireRole('student');

// Fetch active document types
$sql = "SELECT * FROM document_types WHERE active = 1 ORDER BY document_name ASC";
$documents = $auth->getRows($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Request Document - SPVAI Records Office</title>
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
            <li class="active"><a href="request_document.php">Request Document</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Available Documents</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php foreach($documents as $doc): ?>
                        <div class="col-md-6">
                            <div class="panel panel-default" style="margin-bottom: 20px;">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><?= htmlspecialchars($doc['document_name']); ?></h4>
                                </div>
                                <div class="panel-body">
                                    <p><?= htmlspecialchars($doc['description'] ?? 'No description available.'); ?></p>
                                    <p><strong>Processing:</strong> <?= htmlspecialchars($doc['processing_days']); ?> working days</p>
                                    <p><strong>Fee:</strong> ₱<?= number_format($doc['fee'], 2); ?></p>
                                    <button class="btn btn-info btn-block btn-request"
                                            data-id="<?= $doc['document_id']; ?>"
                                            data-name="<?= htmlspecialchars($doc['document_name']); ?>">
                                        Request Document
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="modal-request" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Document Request: <span id="doc-name-display"></span></h4>
            </div>
            <form id="form-request">
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="input-doc-id">
                    <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                    <div class="form-group">
                        <label for="purpose">Purpose of Request</label>
                        <textarea name="purpose" id="purpose" class="form-control" rows="3" required placeholder="e.g., Scholarship application, Employment, etc."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="copies">Number of Copies</label>
                        <input type="number" name="copies" id="copies" class="form-control" min="1" max="10" value="1" required>
                        <span class="help-block">Maximum 10 copies per request.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
$(document).on('click', '.btn-request', function() {
    var docId = $(this).data('id');
    var docName = $(this).data('name');

    $('#input-doc-id').val(docId);
    $('#doc-name-display').text(docName);
    $('#modal-request').modal('show');
});

$(document).on('submit', '#form-request', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Submitting...');

    $.ajax({
        url: 'data/create_request.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                window.location = data.url;
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Submit Request');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Submit Request');
        }
    });
});
</script>
</body>
</html>
