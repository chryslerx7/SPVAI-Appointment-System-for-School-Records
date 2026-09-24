<?php
require_once('layouts/student_header.php');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: my_requests.php");
    exit();
}

// Fetch request details and strictly validate ownership (IDOR Protection)
$sql = "SELECT r.*, dt.document_name, dt.fee
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";

$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    header("Location: my_requests.php?error=unauthorized");
    exit();
}

// Generate Reference Number
$year = date('Y', strtotime($request['created_at']));
$refNumber = sprintf("SPVAI-%s-%07d", $year, $requestId);

// Fetch latest payment for this request (ownership already validated above)
$paySql = "SELECT * FROM payments WHERE request_id = ? ORDER BY created_at DESC LIMIT 1";
$payment = $auth->getRow($paySql, [$requestId]);

// Status Logic for Timeline
$statuses = ['Pending', 'Approved', 'Processing', 'Ready', 'Completed'];
$currentStatus = $request['status'];
$currentIndex = array_search($currentStatus, $statuses);
if ($currentIndex === false) {
    // Handle Rejected/Cancelled outside the linear flow
    $currentIndex = -1;
}
?>

<div class="max-w-4xl mx-auto">
    <header class="mb-12 flex justify-between items-end">
        <div>
            <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Request Details</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide"><?= $refNumber; ?></p>
        </div>
        <a href="my_requests.php" class="px-4 py-2 border-2 border-black bg-white font-black text-xs uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
            ← Back to List
        </a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Document Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Document Type</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['document_name']); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Submission Date</p>
                        <p class="text-lg font-bold"><?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></p>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Purpose</p>
                        <p class="text-lg font-medium leading-relaxed"><?= nl2br(htmlspecialchars($request['purpose'])); ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Copies</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($request['copies']); ?> Copy/ies</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black uppercase text-gray-500">Total Fee</p>
                        <p class="text-lg font-black text-green-700">₱<?= number_format($request['fee'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Appointment & Remarks</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <p class="text-xs font-black uppercase text-gray-500">Scheduled Appointment</p>
                        <?php
                            $appSql = "SELECT * FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
                            $appointment = $auth->getRow($appSql, [$requestId]);
                            if ($appointment): ?>
                                <div class="p-4 border-2 border-black bg-gray-50">
                                    <p class="font-black text-lg"><?= date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p class="font-bold text-gray-600"><?= date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <span class="inline-block mt-2 px-2 py-1 border-2 border-black bg-blue-500 text-white text-[10px] font-black uppercase">
                                        <?= htmlspecialchars($appointment['status']); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <p class="text-sm italic text-gray-500">No appointment scheduled.</p>
                            <?php endif; ?>
                        </div>
                    <div class="space-y-3">
                        <p class="text-xs font-black uppercase text-gray-500">Office Remarks</p>
                        <div class="p-4 border-2 border-black bg-gray-50 min-h-[80px]">
                            <p class="text-sm font-medium italic">
                                <?= !empty($request['remarks']) ? htmlspecialchars($request['remarks']) : 'No remarks provided yet.'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="payment-section" tabindex="-1" class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Payment</h2>
                <?php if ($payment):
                    $payStatus = $payment['payment_status'] ?? 'Unpaid';
                    $payColor = 'bg-gray-400';
                    switch($payStatus) {
                        case 'Paid': $payColor = 'bg-green-500'; break;
                        case 'Pending Verification': $payColor = 'bg-amber-400'; break;
                        case 'Rejected': $payColor = 'bg-red-500'; break;
                        case 'Refunded': $payColor = 'bg-blue-500'; break;
                    }
                ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Amount</p>
                            <p class="text-lg font-black">₱<?= number_format($payment['amount'], 2); ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Status</p>
                            <span class="inline-block border-2 border-black px-2 py-1 text-[10px] font-black uppercase text-white <?= $payColor; ?>">
                                <?= htmlspecialchars($payStatus); ?>
                            </span>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Method</p>
                            <p class="text-lg font-bold"><?= htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-gray-500">Reference</p>
                            <p class="text-lg font-bold"><?= htmlspecialchars($payment['reference_number'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm italic text-gray-500">No payment submitted yet. Amount due: <span class="font-black not-italic">₱<?= number_format($request['fee'] ?? 0, 2); ?></span></p>
                <?php endif; ?>
                <a href="payments.php" class="inline-block mt-6 px-6 py-2 border-2 border-black bg-brutal-yellow font-black uppercase text-xs shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Go to Payment Center
                </a>
            </div>
        </div>
    </div>

        <!-- Sidebar: Status Tracker -->
        <div class="lg:col-span-1">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6 sticky top-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">Request Status</h2>

                <?php if ($currentIndex === -1): ?>
                    <div class="text-center p-6 border-4 border-red-500 bg-red-50">
                        <p class="text-red-600 font-black uppercase text-lg"><?= $currentStatus; ?></p>
                        <p class="text-xs text-red-500 font-bold">This request is no longer active.</p>
                    </div>
                <?php else: ?>
                    <div class="relative space-y-8">
                        <?php foreach($statuses as $index => $status):
                            $isCompleted = $index <= $currentIndex;
                            $isCurrent = $index === $currentIndex;
                            $color = $isCompleted ? 'bg-green-500' : 'bg-gray-200';
                            if ($isCurrent) $color = 'bg-brutal-yellow';
                        ?>
                            <div class="flex items-center gap-4 relative">
                                <?php if($index > 0): ?>
                                    <div class="absolute -top-8 left-4 w-0.5 h-8 <?= $isCompleted ? 'bg-green-500' : 'bg-gray-200' ?>"></div>
                                <?php endif; ?>

                                <div class="w-8 h-8 border-2 border-black rounded-none flex items-center justify-center z-10 <?= $color ?> shadow-brutal">
                                    <?php if($isCompleted): ?>
                                        <span class="text-black font-black text-xs">✓</span>
                                    <?php else: ?>
                                        <span class="text-black font-black text-xs"><?= $index + 1; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-black uppercase <?= $isCurrent ? 'text-black' : 'text-gray-400' ?> <?= $isCurrent ? 'underline decoration-4' : '' ?>">
                                        <?= $status; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
// Payment deep-link (P9-016): request_details.php?id=X&section=payment scrolls to the payment card.
// Plain #payment-section anchors work natively without this script.
(function() {
    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('section') !== 'payment') return;
        var el = document.getElementById('payment-section');
        if (!el) return;
        el.scrollIntoView({ block: 'start' });
        el.focus({ preventScroll: true });
    } catch (e) { /* no-op: deep-link is progressive enhancement */ }
})();
</script>
