<?php
require_once('layouts/admin_header.php');

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

<div class="max-w-6xl mx-auto">
    <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Manage Request</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide"><?= $refNumber; ?></p>
        </div>
        <a href="requests.php" class="px-4 py-2 border-2 border-black bg-white font-black text-xs uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
            ← Back to Requests
        </a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Student & Payment -->
        <div class="space-y-8">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Student Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Student ID</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['student_id']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Full Name</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Email</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Phone</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['phone'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Payment Status</h2>
                <?php if ($payment): ?>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-black uppercase text-gray-500">Amount</p>
                            <p class="text-lg font-black">₱<?= number_format($payment['amount'], 2); ?></p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-black uppercase text-gray-500">Method</p>
                            <p class="text-sm font-bold"><?= htmlspecialchars($payment['payment_method']); ?></p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-black uppercase text-gray-500">Reference</p>
                            <p class="text-sm font-bold"><?= htmlspecialchars($payment['reference_number']); ?></p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-black uppercase text-gray-500">Status</p>
                            <span class="border-2 border-black px-2 py-0.5 text-[10px] font-black uppercase <?= $payment['payment_status'] == 'Paid' ? 'bg-green-500 text-white' : ($payment['payment_status'] == 'Pending Verification' ? 'bg-amber-400' : 'bg-red-500 text-white'); ?>">
                                <?= htmlspecialchars($payment['payment_status']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-black uppercase text-gray-500">Verified By</p>
                            <p class="text-sm font-bold"><?= htmlspecialchars($payment['verified_by'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm italic text-gray-500">No payment record found for this request.</p>
                <?php endif; ?>
                <a href="payments.php?request_id=<?= $requestId; ?>" class="block mt-6 text-center py-2 border-2 border-black bg-white font-black uppercase text-xs shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    View All Payments
                </a>
            </div>
        </div>

        <!-- Right Column: Request, Appointment, Status -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Request Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Document</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['document_name']); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Copies</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['copies']); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Date Submitted</p>
                        <p class="text-lg font-bold"><?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Current Status</p>
                        <span class="inline-block border-2 border-black px-2 py-1 text-[10px] font-black uppercase <?= $request['status'] == 'Ready' ? 'bg-green-500 text-white' : ($request['status'] == 'Pending' ? 'bg-amber-400' : 'bg-gray-200'); ?>">
                            <?= htmlspecialchars($request['status']); ?>
                        </span>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Purpose</p>
                        <p class="text-lg font-medium leading-relaxed italic">"<?= nl2br(htmlspecialchars($request['purpose'])); ?>"</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Appointment</h2>
                <?php if ($appointment): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Scheduled Date & Time</p>
                            <p class="text-lg font-bold"><?= date('F j, Y', strtotime($appointment['appointment_date'])); ?> at <?= date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Status</p>
                            <span class="inline-block border-2 border-black px-2 py-1 text-[10px] font-black uppercase bg-blue-500 text-white">
                                <?= htmlspecialchars($appointment['status']); ?>
                            </span>
                        </div>
                        <div class="md:col-span-2 space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Appointment Remarks</p>
                            <p class="text-sm font-medium"><?= htmlspecialchars($appointment['remarks'] ?? 'No remarks.'); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm italic text-gray-500">No appointment scheduled for this request.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Admin Management</h2>
                <form id="form-update-status" class="space-y-6">
                    <input type="hidden" name="request_id" value="<?= $requestId; ?>">
                    <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-xs font-black uppercase">Update Status</label>
                            <select name="status" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold">
                                <option value="Pending" <?= $request['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?= $request['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Rejected" <?= $request['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="Processing" <?= $request['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Ready" <?= $request['status'] == 'Ready' ? 'selected' : ''; ?>>Ready</option>
                                <option value="Completed" <?= $request['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?= $request['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full py-3 bg-black text-white border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                                Update Request
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-black uppercase">Admin Remarks</label>
                        <textarea id="admin-remarks" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-medium" rows="3"><?= htmlspecialchars($request['remarks'] ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('layouts/admin_footer.php'); ?>
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
