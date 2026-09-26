<?php
require_once('layouts/student_header.php');

$user = $auth->getCurrentUser();
?>

<div class="max-w-3xl mx-auto">
    <header class="mb-12">
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">My Profile</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Manage your account and contact information.</p>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
        <div class="flex items-center gap-6 mb-8">
            <div id="profile-avatar" class="w-24 h-24 shrink-0 bg-brutal-yellow border-4 border-black flex items-center justify-center text-4xl font-black shadow-brutal">
                <?= strtoupper(substr($user['first_name'], 0, 1)) . strtoupper(substr($user['last_name'], 0, 1)); ?>
            </div>
            <div class="min-w-0">
                <h2 id="profile-name" class="text-2xl md:text-3xl font-black uppercase tracking-tighter break-words"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p class="text-sm font-bold text-gray-500 uppercase"><?= $user['role']; ?> Account</p>
            </div>
        </div>

        <div id="profile-message" class="hidden mb-6 p-4 border-2 border-black font-bold text-sm" role="status" aria-live="polite"></div>

        <form id="form-profile" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label for="profile-first-name" class="block text-xs font-black uppercase text-gray-500">First Name</label>
                    <input type="text" id="profile-first-name" name="first_name" maxlength="100" required value="<?= htmlspecialchars($user['first_name']); ?>" class="w-full text-lg font-bold p-3 border-2 border-black rounded-none focus:outline-none focus:ring-2 focus:ring-black break-words">
                </div>
                <div class="space-y-1">
                    <label for="profile-last-name" class="block text-xs font-black uppercase text-gray-500">Last Name</label>
                    <input type="text" id="profile-last-name" name="last_name" maxlength="100" required value="<?= htmlspecialchars($user['last_name']); ?>" class="w-full text-lg font-bold p-3 border-2 border-black rounded-none focus:outline-none focus:ring-2 focus:ring-black break-words">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-gray-500">Student ID</label>
                    <p class="text-lg font-bold p-3 border-2 border-black bg-gray-50 break-words"><?= htmlspecialchars($user['student_id']); ?></p>
                </div>
                <div class="space-y-1">
                    <label for="profile-email" class="block text-xs font-black uppercase text-gray-500">Email Address</label>
                    <input type="email" id="profile-email" name="email" maxlength="150" required value="<?= htmlspecialchars($user['email']); ?>" class="w-full text-lg font-bold p-3 border-2 border-black rounded-none focus:outline-none focus:ring-2 focus:ring-black break-words">
                </div>
                <div class="space-y-1">
                    <label for="profile-phone" class="block text-xs font-black uppercase text-gray-500">Phone Number</label>
                    <input type="tel" id="profile-phone" name="phone" maxlength="20" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Not provided" class="w-full text-lg font-bold p-3 border-2 border-black rounded-none focus:outline-none focus:ring-2 focus:ring-black break-words">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-gray-500">Account Status</label>
                    <p class="text-lg font-bold p-3 border-2 border-black bg-green-100 text-green-800">Active</p>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t-4 border-black flex flex-col-reverse sm:flex-row justify-end gap-4">
                <a href="profile.php" class="px-6 py-2 border-2 border-black bg-white font-black uppercase text-sm text-center shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 border-2 border-black bg-brutal-yellow font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
<script>
$(document).on('submit', '#form-profile', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');
    var msgBox = $('#profile-message');

    submitBtn.prop('disabled', true).text('Saving...');
    msgBox.addClass('hidden').removeClass('bg-green-200 bg-red-200').text('');

    $.ajax({
        url: 'data/update_profile.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            if (data.valid) {
                msgBox.removeClass('hidden').addClass('bg-green-200').text(data.msg);
                if (data.first_name && data.last_name) {
                    $('#profile-name').text(data.first_name + ' ' + data.last_name);
                    $('#profile-avatar').text(
                        (data.first_name.charAt(0) + data.last_name.charAt(0)).toUpperCase()
                    );
                }
            } else {
                msgBox.removeClass('hidden').addClass('bg-red-200').text(data.msg);
            }
            submitBtn.prop('disabled', false).text('Save Changes');
        },
        error: function() {
            msgBox.removeClass('hidden').addClass('bg-red-200').text('An error occurred. Please try again.');
            submitBtn.prop('disabled', false).text('Save Changes');
        }
    });
});
</script>
