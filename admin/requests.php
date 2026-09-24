<?php
require_once __DIR__ . '/../layouts/admin_header.php';

// Get search and sort parameters
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

// Whitelist sorting
$allowedSort = ['created_at', 'status', 'document_id'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'created_at';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Pagination
$limit = 20;
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $limit;

// Build Query
$sql = "SELECT r.request_id, r.status, r.created_at,
               u.first_name, u.last_name, u.student_id,
               dt.document_name
        FROM requests r
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
if ($search) {
    $sql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.student_id LIKE ? OR dt.document_name LIKE ? OR r.status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY $sortBy $order LIMIT $limit OFFSET $offset";

$requests = $auth->getRows($sql, $params);

// Get total for pagination
$countSql = "SELECT COUNT(*) as total FROM requests r
             JOIN users u ON r.user_id = u.user_id
             JOIN document_types dt ON r.document_id = dt.document_id";
if ($search) {
    $countSql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.student_id LIKE ? OR dt.document_name LIKE ? OR r.status LIKE ?)";
}
$total = $auth->getRow($countSql, $params)['total'] ?? 0;
$totalPages = ceil($total / $limit);
?>

<div class="max-w-7xl mx-auto">
    <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">Document Requests</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Review and manage all incoming student requests.</p>
        </div>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-6 border-b-4 border-black bg-gray-50">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <input type="text" name="search" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold"
                           placeholder="Search student, ID, doc..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select name="sort" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : ''; ?>>Date</option>
                        <option value="status" <?= $sortBy == 'status' ? 'selected' : ''; ?>>Status</option>
                        <option value="document_id" <?= $sortBy == 'document_id' ? 'selected' : ''; ?>>Document</option>
                    </select>
                    <select name="order" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                    <button type="submit" class="px-6 py-3 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                        Filter
                    </button>
                    <a href="requests.php" class="px-6 py-3 border-2 border-black bg-white font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white uppercase text-xs font-black tracking-widest">
                        <th class="p-4 border-r-2 border-gray-800">Ref #</th>
                        <th class="p-4 border-r-2 border-gray-800">Student</th>
                        <th class="p-4 border-r-2 border-gray-800">Student ID</th>
                        <th class="p-4 border-r-2 border-gray-800">Document</th>
                        <th class="p-4 border-r-2 border-gray-800 text-center">Status</th>
                        <th class="p-4 border-r-2 border-gray-800">Date</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center font-bold text-gray-500 uppercase">No requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($requests as $req):
                            $year = date('Y', strtotime($req['created_at']));
                            $refNum = sprintf("SPVAI-%s-%07d", $year, $req['request_id']);

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
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 border-r-2 border-black font-black text-sm"><?= $refNum; ?></td>
                                <td class="p-4 border-r-2 border-black font-bold text-sm"><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                <td class="p-4 border-r-2 border-black font-medium text-sm"><?= htmlspecialchars($req['student_id']); ?></td>
                                <td class="p-4 border-r-2 border-black font-bold text-sm"><?= htmlspecialchars($req['document_name']); ?></td>
                                <td class="p-4 border-r-2 border-black text-center">
                                    <span class="<?= $statusColor; ?> border-2 border-black text-white px-2 py-1 text-[10px] font-black uppercase inline-block">
                                        <?= htmlspecialchars($req['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td class="p-4 text-right">
                                    <a href="request_details.php?id=<?= $req['request_id']; ?>" class="px-3 py-2 border-2 border-black bg-white font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 bg-gray-50 border-t-4 border-black flex justify-center gap-2">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                    <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&sort=<?= $sortBy; ?>&order=<?= $order; ?>"
                       class="px-4 py-2 border-2 border-black font-black uppercase text-sm transition-all <?= $i == $page ? 'bg-brutal-yellow shadow-none' : 'bg-white shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1' ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>
