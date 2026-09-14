<?php
require_once('layouts/student_header.php');

$appId = $_GET['id'] ?? null;

if (!$appId) {
    header("Location: my_requests.php");
    exit();
}

// Fetch appointment and request details, verify ownership
$sql = "SELECT a.*, r.request_id, dt.document_name
        FROM appointments a
        JOIN requests r ON a.request_id = r.request_id
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE a.appointment_id = ? AND r.user_id = ? LIMIT 1";

$app = $auth->getRow($sql, [$appId, $_SESSION['user_id']]);

if (!$app) {
    header("Location: my_requests.php?error=unauthorized");
    exit();
}

// Reference Number
$year = date('Y', strtotime($app['created_at']));
$refNumber = sprintf("SPVAI-%s-%07d", $year, $app['request_id']);
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <!-- Success Header -->
        <div class="p-8 border-b-4 border-black bg-green-500 text-white text-center">
            <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Success!</h1>
            <h2 class="text-2xl font-bold uppercase tracking-wide">Appointment Scheduled</h2>
        </div>

        <div class="p-8">
            <p class="text-lg font-bold text-center mb-8">Your appointment has been successfully scheduled. Please bring a valid ID upon arrival.</p>

            <!-- Details Card -->
            <div class="border-4 border-black p-6 bg-gray-50 space-y-4">
                <div class="flex justify-between border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Request Ref #</span>
                    <span class="font-black"><?= $refNumber; ?></span>
                </div>
                <div class="flex justify-between border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Document</span>
                    <span class="font-black"><?= htmlspecialchars($app['document_name']); ?></span>
                </div>
                <div class="flex justify-between border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Date</span>
                    <span class="font-black"><?= date('F j, Y', strtotime($app['appointment_date'])); ?></span>
                </div>
                <div class="flex justify-between border-b-2 border-black pb-2">
                    <span class="text-xs font-black uppercase text-gray-500">Time</span>
                    <span class="font-black"><?= date('h:i A', strtotime($app['appointment_time'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs font-black uppercase text-gray-500">Status</span>
                    <span class="px-2 py-0.5 bg-amber-400 border-2 border-black text-white text-[10px] font-black uppercase">
                        <?= htmlspecialchars($app['status']); ?>
                    </span>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="my_requests.php" class="px-8 py-3 bg-brutal-yellow border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Back to My Requests
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
