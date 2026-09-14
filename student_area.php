<?php
require_once('layouts/student_header.php');

// Fetch summary statistics
$userId = $_SESSION['user_id'];

$activeReqCount = $auth->getRow("SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status NOT IN ('Completed', 'Cancelled')", [$userId])['total'];
$appCount = $auth->getRow("SELECT COUNT(*) as total FROM appointments a JOIN requests r ON a.request_id = r.request_id WHERE r.user_id = ? AND a.status != 'Cancelled'", [$userId])['total'];
$readyCount = $auth->getRow("SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status = 'Ready'", [$userId])['total'];

// Fetch last 3 requests
$recentRequests = $auth->getRows("SELECT r.*, dt.document_name FROM requests r JOIN document_types dt ON r.document_id = dt.document_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 3", [$userId]);
?>

<div class="max-w-6xl mx-auto">
    <header class="mb-12">
        <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Good Morning, <?= htmlspecialchars($user['first_name']); ?>!</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Welcome back to the Records Office Portal.</p>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-500 mb-2">Active Requests</p>
            <p class="text-6xl font-black"><?= $activeReqCount; ?></p>
        </div>
        <div class="bg-white border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-500 mb-2">Appointments</p>
            <p class="text-6xl font-black"><?= $appCount; ?></p>
        </div>
        <div class="bg-brutal-yellow border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-800 mb-2">Ready Documents</p>
            <p class="text-6xl font-black"><?= $readyCount; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Account Info -->
        <div class="lg:col-span-1">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <h2 class="text-xl font-black uppercase mb-6 border-b-4 border-black pb-2">My Profile</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Student ID</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($user['student_id']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Full Name</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Email</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-500">Phone</p>
                        <p class="text-lg font-bold"><?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    </div>
                </div>
                <a href="profile.php" class="block mt-8 text-center py-2 border-2 border-black font-black uppercase text-sm hover:bg-brutal-yellow transition-colors">Edit Profile</a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="lg:col-span-2">
            <div class="bg-white border-4 border-black shadow-brutal-lg p-6">
                <div class="flex justify-between items-center mb-6 border-b-4 border-black pb-2">
                    <h2 class="text-xl font-black uppercase">Recent Requests</h2>
                    <a href="my_requests.php" class="text-xs font-black uppercase underline hover:text-brutal-yellow">View All</a>
                </div>

                <?php if (empty($recentRequests)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-500 font-bold uppercase">No requests found.</p>
                        <a href="request_document.php" class="inline-block mt-4 px-6 py-2 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">Request a Document</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($recentRequests as $req): ?>
                            <div class="border-2 border-black p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:bg-gray-50 transition-colors">
                                <div>
                                    <p class="text-xs font-black text-gray-500 uppercase">#<?= $req['request_id']; ?></p>
                                    <p class="text-lg font-bold"><?= htmlspecialchars($req['document_name']); ?></p>
                                    <p class="text-sm font-medium text-gray-600"><?= date('M d, Y', strtotime($req['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center gap-4 w-full md:w-auto">
                                    <span class="badge-brutal <?= $req['status'] == 'Ready' ? 'bg-green-500' : ($req['status'] == 'Rejected' ? 'bg-red-500' : 'bg-amber-400'); ?> text-white px-3 py-1 text-xs font-black uppercase">
                                        <?= $req['status']; ?>
                                    </span>
                                    <a href="request_details.php?id=<?= $req['request_id']; ?>" class="px-4 py-2 border-2 border-black bg-white font-black text-xs uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all whitespace-nowrap">
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
