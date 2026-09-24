<?php
require_once('layouts/student_header.php');

$userId = $_SESSION['user_id'];

// Fetch all requests that have a fee > 0
$sql = "SELECT r.request_id, r.status as req_status, r.created_at, dt.document_name, dt.fee, p.payment_status, p.reference_number, p.payment_id
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        LEFT JOIN payments p ON r.request_id = p.request_id
        WHERE r.user_id = ? AND dt.fee > 0
        ORDER BY r.created_at DESC";

$payments = $auth->getRows($sql, [$userId]);
?>

<div class="max-w-6xl mx-auto">
    <header class="mb-12">
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">Payment Center</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Submit and track your document payment verifications.</p>
    </header>

    <?php if (empty($payments)): ?>
        <div class="bg-white border-4 border-black shadow-brutal-lg p-12 text-center">
            <h2 class="text-2xl font-black uppercase mb-4">No Payments Due</h2>
            <p class="text-gray-600 font-medium mb-8">You currently have no requests that require payment.</p>
            <a href="request_document.php" class="inline-block px-8 py-3 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Request a Document
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-8">
            <?php foreach($payments as $pay):
                $status = $pay['payment_status'] ?? 'Unpaid';
                $statusColor = 'bg-gray-400';
                switch($status) {
                    case 'Paid': $statusColor = 'bg-green-500'; break;
                    case 'Pending Verification': $statusColor = 'bg-amber-400'; break;
                    case 'Rejected': $statusColor = 'bg-red-500'; break;
                    case 'Refunded': $statusColor = 'bg-blue-500'; break;
                    case 'Unpaid': $statusColor = 'bg-gray-400'; break;
                }
            ?>
                <div class="bg-white border-4 border-black shadow-brutal p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-xs font-black uppercase text-gray-500">#<?= $pay['request_id']; ?></span>
                            <span class="<?= $statusColor; ?> border-2 border-black text-white px-2 py-0.5 text-[10px] font-black uppercase">
                                <?= $status; ?>
                            </span>
                        </div>
                        <h3 class="text-xl font-black uppercase"><?= htmlspecialchars($pay['document_name']); ?></h3>
                        <p class="text-lg font-bold">Amount Due: ₱<?= number_format($pay['fee'], 2); ?></p>
                        <?php if ($pay['reference_number']): ?>
                            <p class="text-xs font-bold text-gray-600 mt-2">Ref: <?= htmlspecialchars($pay['reference_number']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="w-full md:w-auto">
                        <?php if ($status === 'Unpaid' || $status === 'Rejected'): ?>
                            <button class="btn-pay-trigger w-full md:w-auto px-6 py-2 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all"
                                    data-id="<?= $pay['request_id']; ?>"
                                    data-fee="<?= $pay['fee']; ?>">
                                Submit Payment
                            </button>
                        <?php else: ?>
                            <div class="text-right">
                                <p class="text-xs font-black uppercase text-gray-500">Payment Status</p>
                                <p class="font-bold"><?= $status; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="modal-payment" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white border-4 border-black shadow-brutal-lg w-full max-w-md overflow-hidden">
        <div class="p-6 border-b-4 border-black flex justify-between items-center bg-brutal-yellow">
            <h4 class="text-xl font-black uppercase tracking-tighter">Payment Submission</h4>
            <button class="close-modal text-2xl font-black leading-none hover:text-red-500">&times;</button>
        </div>
        <form id="form-payment" class="p-6 space-y-6">
            <input type="hidden" name="request_id" id="input-pay-req-id">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="bg-gray-50 p-4 border-2 border-black mb-4">
                <p class="text-xs font-black uppercase text-gray-500">Amount to Pay</p>
                <p id="pay-amount-display" class="text-2xl font-black">₱0.00</p>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Payment Method</label>
                <select name="payment_method" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold">
                    <option value="GCash">GCash</option>
                    <option value="Maya">Maya</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash (Over-the-Counter)</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Reference Number</label>
                <input type="text" name="reference_number" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold" required placeholder="Enter transaction ID">
            </div>

            <div class="flex gap-4">
                <button type="button" class="close-modal flex-1 py-3 border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
$(document).on('click', '.btn-pay-trigger', function() {
    var reqId = $(this).data('id');
    var fee = $(this).data('fee');

    $('#input-pay-req-id').val(reqId);
    $('#pay-amount-display').text('₱' + parseFloat(fee).toLocaleString(undefined, {minimumFractionDigits: 2}));
    $('#modal-payment').removeClass('hidden');
});

$(document).on('click', '.close-modal', function() {
    $('#modal-payment').addClass('hidden');
});

$(document).on('submit', '#form-payment', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Submitting...');

    $.ajax({
        url: 'data/submit_payment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Submit');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Submit');
        }
    });
});
</script>
