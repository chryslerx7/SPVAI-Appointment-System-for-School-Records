<?php
require_once('layouts/student_header.php');

// Fetch requests for current user
$sql = "SELECT r.request_id, r.status, r.created_at, dt.document_name
        FROM requests r
        JOIN document_types dt ON r.document_id = dt.document_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC";

$myRequests = $auth->getRows($sql, [$_SESSION['user_id']]);
?>

<div class="max-w-6xl mx-auto">
    <header class="mb-12">
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">My Requests</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Track and manage your official document requests.</p>
    </header>

    <?php if (empty($myRequests)): ?>
        <div class="bg-white border-4 border-black shadow-brutal-lg p-12 text-center">
            <h2 class="text-2xl font-black uppercase mb-4">No requests found</h2>
            <p class="text-gray-600 font-medium mb-8">You haven't submitted any document requests yet.</p>
            <a href="request_document.php" class="inline-block px-8 py-3 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Request Your First Document
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-6">
            <!-- Table Header for Desktop -->
            <div class="hidden md:grid grid-cols-5 p-4 bg-black text-white font-black uppercase text-xs tracking-widest">
                <div class="col-span-1">Reference #</div>
                <div class="col-span-1">Document</div>
                <div class="col-span-1">Date</div>
                <div class="col-span-1 text-center">Status</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>

            <?php foreach($myRequests as $req):
                $year = date('Y', strtotime($req['created_at']));
                $refNum = sprintf("SPVAI-%s-%07d", $year, $req['request_id']);

                // Status Color Mapping
                $statusColor = 'bg-gray-400';
                switch($req['status']) {
                    case 'Pending': $statusColor = 'bg-amber-400'; break;
                    case 'Approved': $statusColor = 'bg-blue-500'; break;
                    case 'Processing': $statusColor = 'bg-indigo-500'; break;
                    case 'Ready': $statusColor = 'bg-green-500'; break;
                    case 'Completed': $statusColor = 'bg-green-800'; break;
                    case 'Rejected': $statusColor = 'bg-red-500'; break;
                    case 'Cancelled': $statusColor = 'bg-gray-400'; break;
                }
            ?>
                <div class="bg-white border-4 border-black shadow-brutal p-4 grid grid-cols-1 md:grid-cols-5 gap-4 items-center hover:bg-gray-50 transition-colors">
                    <div class="col-span-1">
                        <p class="text-xs font-black uppercase text-gray-500 md:hidden">Reference #</p>
                        <p class="font-black text-sm md:text-base"><?= $refNum; ?></p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-xs font-black uppercase text-gray-500 md:hidden">Document</p>
                        <p class="font-bold"><?= htmlspecialchars($req['document_name']); ?></p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-xs font-black uppercase text-gray-500 md:hidden">Date</p>
                        <p class="text-sm font-medium"><?= date('M d, Y', strtotime($req['created_at'])); ?></p>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <p class="text-xs font-black uppercase text-gray-500 md:hidden mr-2">Status</p>
                        <span class="<?= $statusColor; ?> border-2 border-black text-white px-3 py-1 text-[10px] font-black uppercase">
                            <?= htmlspecialchars($req['status']); ?>
                        </span>
                    </div>
                    <div class="col-span-1 flex flex-wrap gap-2 justify-end">
                        <a href="request_details.php?id=<?= $req['request_id']; ?>" class="px-3 py-2 border-2 border-black bg-white font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                            Details
                        </a>
                        <?php
                            $appSql = "SELECT appointment_id FROM appointments WHERE request_id = ? AND status != 'Cancelled' LIMIT 1";
                            $hasApp = $auth->getRow($appSql, [$req['request_id']]);
                            $allowedStatuses = ['Pending', 'Approved', 'Processing', 'Ready'];
                            if (!$hasApp && in_array($req['status'], $allowedStatuses)) {
                                echo '<a href="appointments.php?id=' . $req['request_id'] . '" class="px-3 py-2 border-2 border-black bg-brutal-yellow font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">Schedule</a>';
                            }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('layouts/student_footer.php'); ?>
