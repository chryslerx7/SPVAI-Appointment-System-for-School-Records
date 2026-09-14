<?php
require_once('layouts/student_header.php');

// Fetch notifications for current user
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$notifications = $auth->getRows($sql, [$_SESSION['user_id']]);
?>

<div class="max-w-4xl mx-auto">
    <header class="mb-12">
        <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Notifications</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Stay updated on your request and payment status.</p>
    </header>

    <?php if (empty($notifications)): ?>
        <div class="bg-white border-4 border-black shadow-brutal-lg p-12 text-center">
            <h2 class="text-2xl font-black uppercase mb-4">No notifications</h2>
            <p class="text-gray-600 font-medium">You're all caught up!</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach($notifications as $notif): ?>
                <div class="bg-white border-4 border-black shadow-brutal p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 <?= $notif['is_read'] ? 'opacity-70' : 'bg-yellow-50'; ?>" id="notif-<?= $notif['notification_id']; ?>">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <?php if (!$notif['is_read']): ?>
                                <span class="w-3 h-3 bg-red-500 border border-black rounded-full"></span>
                            <?php endif; ?>
                            <span class="text-xs font-black uppercase tracking-widest text-gray-500">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $notif['type'])); ?></span>
                            <span class="text-[10px] font-bold text-gray-400 ml-auto"><?= date('M d, Y h:i A', strtotime($notif['created_at'])); ?></span>
                        </div>
                        <p class="text-lg font-bold leading-tight"><?= htmlspecialchars($notif['message']); ?></p>
                    </div>
                    <div class="flex gap-3">
                        <?php if (!$notif['is_read']): ?>
                            <button class="btn-mark-read px-3 py-2 border-2 border-black bg-white font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all whitespace-nowrap" data-id="<?= $notif['notification_id']; ?>">
                                Mark Read
                            </button>
                        <?php endif; ?>
                        <?php if ($notif['request_id']): ?>
                            <a href="request_details.php?id=<?= $notif['request_id']; ?>" class="px-3 py-2 border-2 border-black bg-brutal-yellow font-black text-[10px] uppercase shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all whitespace-nowrap">
                                View Request
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
$(document).on('click', '.btn-mark-read', function() {
    var notifId = $(this).data('id');
    var btn = $(this);

    $.ajax({
        url: 'data/mark_read.php',
        type: 'POST',
        dataType: 'json',
        data: {
            notification_id: notifId,
            csrf_token: '<?= $auth->generateCsrfToken(); ?>'
        },
        success: function(data) {
            if (data.valid) {
                btn.fadeOut();
                $('#notif-' + notifId).removeClass('bg-yellow-50').addClass('opacity-70');
            } else {
                alert(data.msg);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
});
</script>
