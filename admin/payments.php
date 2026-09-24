<?php
require_once __DIR__ . '/../layouts/admin_header.php';

// Sorting and Filtering
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

$allowedSort = ['created_at', 'payment_status', 'amount'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'created_at';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Optional deep-link filter from admin/request_details.php (?request_id=<ID>).
// Strictly validated: only a positive integer narrows the results; anything
// else is ignored so the page behaves exactly as if no filter was given.
$requestId = filter_var($_GET['request_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

// Build Query
$sql = "SELECT p.*, u.first_name, u.last_name, r.request_id, dt.document_name
        FROM payments p
        JOIN requests r ON p.request_id = r.request_id
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
$conditions = [];
if ($search) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR p.reference_number LIKE ? OR p.payment_status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}
if ($requestId) {
    $conditions[] = "r.request_id = ?";
    $params[] = $requestId;
}
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY $sortBy $order";

$payments = $auth->getRows($sql, $params);
?>

<div class="max-w-7xl mx-auto">
    <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Payment Verification</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Validate and manage student payment submissions.</p>
        </div>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-6 border-b-4 border-black bg-gray-50">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <?php if ($requestId): ?>
                    <input type="hidden" name="request_id" value="<?= (int)$requestId; ?>">
                <?php endif; ?>
                <div class="flex-1 relative">
                    <input type="text" name="search" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold"
                           placeholder="Search reference, student, status..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="flex gap-2">
                    <select name="sort" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : ''; ?>>Date</option>
                        <option value="payment_status" <?= $sortBy == 'payment_status' ? 'selected' : ''; ?>>Status</option>
                        <option value="amount" <?= $sortBy == 'amount' ? 'selected' : ''; ?>>Amount</option>
                    </select>
                    <select name="order" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                    <button type="submit" class="px-6 py-3 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                        Filter
                    </button>
                    <a href="payments.php" class="px-6 py-3 border-2 border-black bg-white font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <?php if ($requestId): ?>
            <div class="px-6 py-3 border-b-4 border-black bg-brutal-yellow flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                <p class="text-xs font-black uppercase">Filtered to request #<?= (int)$requestId; ?></p>
                <a href="payments.php" class="text-xs font-black uppercase underline">Clear filter</a>
            </div>
        <?php endif; ?>

        <!-- Payments Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white uppercase text-xs font-black tracking-widest">
                        <th class="p-4 border-r-2 border-gray-800">Student</th>
                        <th class="p-4 border-r-2 border-gray-800">Ref #</th>
                        <th class="p-4 border-r-2 border-gray-800">Document</th>
                        <th class="p-4 border-r-2 border-gray-800">Amount</th>
                        <th class="p-4 border-r-2 border-gray-800">Method</th>
                        <th class="p-4 border-r-2 border-gray-800 text-center">Status</th>
                        <th class="p-4 border-r-2 border-gray-800">Date</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center font-bold text-gray-500 uppercase">No payment records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($payments as $pay):
                            $statusColor = 'bg-gray-400';
                            switch($pay['payment_status']) {
                                case 'Paid': $statusColor = 'bg-green-500'; break;
                                case 'Pending Verification': $statusColor = 'bg-amber-400'; break;
                                case 'Rejected': $statusColor = 'bg-red-500'; break;
                                case 'Refunded': $statusColor = 'bg-blue-500'; break;
                                case 'Unpaid': $statusColor = 'bg-gray-400'; break;
                            }
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 border-r-2 border-black font-bold text-sm"><?= htmlspecialchars($pay['first_name'] . ' ' . $pay['last_name']); ?></td>
                                <td class="p-4 border-r-2 border-black font-black text-sm"><?= htmlspecialchars($pay['reference_number'] ?? 'N/A'); ?></td>
                                <td class="p-4 border-r-2 border-black font-medium text-sm"><?= htmlspecialchars($pay['document_name']); ?></td>
                                <td class="p-4 border-r-2 border-black font-black text-sm">₱<?= number_format($pay['amount'], 2); ?></td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= htmlspecialchars($pay['payment_method']); ?></td>
                                <td class="p-4 border-r-2 border-black text-center">
                                    <span class="<?= $statusColor; ?> border-2 border-black text-white px-2 py-1 text-[10px] font-black uppercase inline-block">
                                        <?= htmlspecialchars($pay['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                <td class="p-4 text-right">
                                    <button class="btn-verify-pay px-3 py-2 border-2 border-black bg-white font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all"
                                            data-id="<?= $pay['payment_id']; ?>"
                                            data-status="<?= $pay['payment_status']; ?>">
                                        Verify
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Verify Payment Modal -->
<div id="modal-verify-pay" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white border-4 border-black shadow-brutal-lg w-full max-w-md overflow-hidden">
        <div class="p-6 border-b-4 border-black flex justify-between items-center bg-brutal-yellow">
            <h4 class="text-xl font-black uppercase tracking-tighter">Verify Payment</h4>
            <button class="close-modal text-2xl font-black leading-none hover:text-red-500">&times;</button>
        </div>
        <form id="form-verify-pay" class="p-6 space-y-6">
            <input type="hidden" name="payment_id" id="input-pay-id">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Update Status</label>
                <select name="payment_status" id="input-pay-status" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold">
                    <option value="Unpaid">Unpaid</option>
                    <option value="Pending Verification">Pending Verification</option>
                    <option value="Paid">Paid</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Refunded">Refunded</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Verification Remarks</label>
                <textarea name="remarks" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-medium" rows="3"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="button" class="close-modal flex-1 py-3 border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-black text-white border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>
<script>
$(document).on('click', '.btn-verify-pay', function() {
    var payId = $(this).data('id');
    var status = $(this).data('status');

    $('#input-pay-id').val(payId);
    $('#input-pay-status').val(status);
    $('#modal-verify-pay').removeClass('hidden');
});

$(document).on('click', '.close-modal', function() {
    $('#modal-verify-pay').addClass('hidden');
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
                submitBtn.prop('disabled', false).text('Save');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Save');
        }
    });
});
</script>
