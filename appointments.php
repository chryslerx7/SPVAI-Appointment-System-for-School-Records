<?php
require_once('layouts/student_header.php');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    echo "<script>window.location='my_requests.php?error=no_request';</script>";
    exit();
}

// Validate request ownership and eligibility
$sql = "SELECT r.*, dt.document_name
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";
$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    echo "<script>window.location='my_requests.php?error=unauthorized';</script>";
    exit();
}

// Check if an active appointment already exists
$appSql = "SELECT appointment_id FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
$existingApp = $auth->getRow($appSql, [$requestId]);

if ($existingApp) {
    echo "<script>alert('This request already has an active appointment.'); window.location='my_requests.php';</script>";
    exit();
}

// Check request status eligibility (Cannot schedule if Completed, Cancelled, or Rejected)
$forbiddenStatuses = ['Completed', 'Cancelled', 'Rejected'];
if (in_array($request['status'], $forbiddenStatuses)) {
    echo "<script>alert('This request cannot be scheduled due to its current status: " . htmlspecialchars($request['status']) . "'); window.location='my_requests.php';</script>";
    exit();
}
?>

<div class="max-w-3xl mx-auto">
    <header class="mb-12">
        <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Schedule Visit</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Pick a convenient date and time to pick up your document.</p>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
        <div class="mb-8 p-4 bg-gray-50 border-2 border-black flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <p class="text-xs font-black uppercase text-gray-500">Document Request</p>
                <p class="text-xl font-black"><?= htmlspecialchars($request['document_name']); ?></p>
            </div>
            <div class="text-right">
                <p class="text-xs font-black uppercase text-gray-500">Reference</p>
                <p class="text-lg font-bold">SPVAI-<?= date('Y') ?>-<?= sprintf('%07d', $requestId); ?></p>
            </div>
        </div>

        <form id="form-appointment" class="space-y-8">
            <input type="hidden" name="request_id" value="<?= $requestId; ?>">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label for="app-date" class="block text-xs font-black uppercase">1. Select Date</label>
                    <input type="date" name="appointment_date" id="app-date" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold" required>
                </div>

                <div class="space-y-2">
                    <label for="app-time" class="block text-xs font-black uppercase">2. Available Time Slots</label>
                    <select name="appointment_time" id="app-time" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold appearance-none" required disabled>
                        <option value="">-- Select Date First --</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-4 justify-center pt-6">
                <a href="my_requests.php" class="px-8 py-3 border-2 border-black bg-white font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all text-center">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all text-center">
                    Confirm Appointment
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
$(document).on('change', '#app-date', function() {
    var date = $(this).val();
    var timeSelect = $('#app-time');

    if (!date) return;

    timeSelect.prop('disabled', true).html('<option>Loading slots...</option>');

    $.ajax({
        url: 'data/get_slots.php',
        type: 'POST',
        dataType: 'json',
        data: { date: date },
        success: function(data) {
            if (data.valid) {
                var options = '<option value="">-- Choose a Time --</option>';
                data.slots.forEach(function(slot) {
                    var disabled = slot.is_full ? 'disabled' : '';
                    var text = slot.display_time + (slot.is_full ? ' (Fully Booked)' : ' (' + slot.remaining + ' slots left)');
                    options += '<option value="' + slot.time + '" ' + disabled + '>' + text + '</option>';
                });
                timeSelect.html(options).prop('disabled', false);
            } else {
                alert(data.msg);
                timeSelect.html('<option value="">-- Error --</option>');
            }
        },
        error: function() {
            alert('Error fetching available slots.');
            timeSelect.html('<option value="">-- Error --</option>');
        }
    });
});

$(document).on('submit', '#form-appointment', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: 'data/save_appointment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                window.location = data.url;
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Confirm Appointment');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Confirm Appointment');
        }
    });
});
</script>
