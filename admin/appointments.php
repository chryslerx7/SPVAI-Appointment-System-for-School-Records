<?php
require_once('layouts/admin_header.php');

// Sorting and Filtering
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'appointment_date';
$order = $_GET['order'] ?? 'ASC';

$allowedSort = ['appointment_date', 'appointment_time', 'status'];
if (!in_array($sortBy, $allowedSort)) {
    $sortBy = 'appointment_date';
}
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Build Query
$sql = "SELECT a.*, r.request_id, u.first_name, u.last_name, dt.document_name
        FROM appointments a
        JOIN requests r ON a.request_id = r.request_id
        JOIN users u ON r.user_id = u.user_id
        JOIN document_types dt ON r.document_id = dt.document_id";

$params = [];
if ($search) {
    $sql .= " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR r.request_id = ? OR a.status LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $search, $searchTerm];
}

$sql .= " ORDER BY $sortBy $order";

$appointments = $auth->getRows($sql, $params);
?>

<div class="max-w-7xl mx-auto">
    <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Appointment Mgmt</h1>
            <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Review and update student visit schedules.</p>
        </div>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-6 border-b-4 border-black bg-gray-50">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <input type="text" name="search" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold"
                           placeholder="Search student, request, status..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="flex gap-2">
                    <select name="sort" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="appointment_date" <?= $sortBy == 'appointment_date' ? 'selected' : ''; ?>>Date</option>
                        <option value="appointment_time" <?= $sortBy == 'appointment_time' ? 'selected' : ''; ?>>Time</option>
                        <option value="status" <?= $sortBy == 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                    <select name="order" class="border-2 border-black p-3 rounded-none bg-white font-bold focus:outline-none">
                        <option value="ASC" <?= $order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        <option value="DESC" <?= $order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                    </select>
                    <button type="submit" class="px-6 py-3 bg-brutal-yellow border-2 border-black font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                        Filter
                    </button>
                    <a href="appointments.php" class="px-6 py-3 border-2 border-black bg-white font-black uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Appointments Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white uppercase text-xs font-black tracking-widest">
                        <th class="p-4 border-r-2 border-gray-800">Student</th>
                        <th class="p-4 border-r-2 border-gray-800">Ref #</th>
                        <th class="p-4 border-r-2 border-gray-800">Document</th>
                        <th class="p-4 border-r-2 border-gray-800">Date</th>
                        <th class="p-4 border-r-2 border-gray-800">Time</th>
                        <th class="p-4 border-r-2 border-gray-800 text-center">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center font-bold text-gray-500 uppercase">No appointments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($appointments as $app):
                            $year = date('Y', strtotime($app['created_at']));
                            $refNum = sprintf("SPVAI-%s-%07d", $year, $app['request_id']);

                            $statusColor = 'bg-gray-400';
                            switch($app['status']) {
                                case 'Scheduled': $statusColor = 'bg-amber-400'; break;
                                case 'Confirmed': $statusColor = 'bg-blue-500'; break;
                                case 'Completed': $statusColor = 'bg-green-500'; break;
                                case 'Cancelled': $statusColor = 'bg-gray-400'; break;
                                case 'No Show': $statusColor = 'bg-red-500'; break;
                            }
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 border-r-2 border-black font-bold text-sm"><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                <td class="p-4 border-r-2 border-black font-black text-sm"><?= $refNum; ?></td>
                                <td class="p-4 border-r-2 border-black font-medium text-sm"><?= htmlspecialchars($app['document_name']); ?></td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= date('M d, Y', strtotime($app['appointment_date'])); ?></td>
                                <td class="p-4 border-r-2 border-black text-sm font-medium"><?= date('h:i A', strtotime($app['appointment_time'])); ?></td>
                                <td class="p-4 border-r-2 border-black text-center">
                                    <span class="<?= $statusColor; ?> border-2 border-black text-white px-2 py-1 text-[10px] font-black uppercase inline-block">
                                        <?= htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="btn-manage-app px-3 py-2 border-2 border-black bg-white font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all"
                                            data-id="<?= $app['appointment_id']; ?>"
                                            data-status="<?= $app['status']; ?>">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Manage Appointment Modal -->
<div id="modal-manage-app" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white border-4 border-black shadow-brutal-lg w-full max-w-md overflow-hidden">
        <div class="p-6 border-b-4 border-black flex justify-between items-center bg-brutal-yellow">
            <h4 class="text-xl font-black uppercase tracking-tighter">Update Status</h4>
            <button class="close-modal text-2xl font-black leading-none hover:text-red-500">&times;</button>
        </div>
        <form id="form-update-app" class="p-6 space-y-6">
            <input type="hidden" name="appointment_id" id="input-app-id">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">New Status</label>
                <select name="status" id="input-app-status" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold">
                    <option value="Scheduled">Scheduled</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="No Show">No Show</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase">Remarks</label>
                <textarea name="remarks" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-medium" rows="3"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="button" class="close-modal flex-1 py-3 border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-black text-white border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once('layouts/admin_footer.php'); ?>
<script>
$(document).on('click', '.btn-manage-app', function() {
    var appId = $(this).data('id');
    var status = $(this).data('status');

    $('#input-app-id').val(appId);
    $('#input-app-status').val(status);
    $('#modal-manage-app').removeClass('hidden');
});

$(document).on('click', '.close-modal', function() {
    $('#modal-manage-app').addClass('hidden');
});

$(document).on('submit', '#form-update-app', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');

    submitBtn.prop('disabled', true).text('Updating...');

    $.ajax({
        url: 'update_appointment.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                submitBtn.prop('disabled', false).text('Update');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Update');
        }
    });
});
</script>
