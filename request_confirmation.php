<?php
require_once('layouts/student_header.php');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    header("Location: request_document.php");
    exit();
}

// Fetch request details and validate ownership
$sql = "SELECT r.*, dt.document_name, dt.fee
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.request_id = ? AND r.user_id = ? LIMIT 1";

$request = $auth->getRow($sql, [$requestId, $_SESSION['user_id']]);

if (!$request) {
    header("Location: my_requests.php?error=invalid_request");
    exit();
}

// Generate Reference Number (Consistent with create_request.php)
$year = date('Y');
$refNumber = sprintf("SPVAI-%s-%07d", $year, $requestId);
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <!-- Success Header -->
        <div class="p-8 border-b-4 border-black bg-green-500 text-white text-center">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">Success!</h1>
            <h2 class="text-2xl font-bold uppercase tracking-wide">Request Submitted</h2>
        </div>

        <div class="p-8">
            <p class="text-lg font-bold text-center mb-8">Your request has been successfully submitted and is now being processed by the Records Office.</p>

            <!-- Details Card -->
            <div class="border-4 border-black p-6 bg-gray-50 space-y-4">
                <div class="flex justify-between gap-4 border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Reference #</span>
                    <span class="font-black"><?= $refNumber; ?></span>
                </div>
                <div class="flex justify-between gap-4 border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Document</span>
                    <span class="font-black"><?= htmlspecialchars($request['document_name']); ?></span>
                </div>
                <div class="flex justify-between gap-4 border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Copies</span>
                    <span class="font-black"><?= htmlspecialchars($request['copies']); ?></span>
                </div>
                <div class="flex justify-between gap-4 border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Purpose</span>
                    <span class="font-black text-right max-w-xs"><?= htmlspecialchars($request['purpose']); ?></span>
                </div>
                <div class="flex justify-between gap-4 border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Submission Date</span>
                    <span class="font-black"><?= date('M d, Y h:i A', strtotime($request['created_at'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs font-black uppercase text-gray-500">Status</span>
                    <span class="px-2 py-0.5 bg-amber-400 border-2 border-black text-white text-[10px] font-black uppercase">
                        <?= htmlspecialchars($request['status']); ?>
                    </span>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="my_requests.php" class="px-8 py-3 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    View My Requests
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
