<?php
require_once('layouts/student_header.php');

$user = $auth->getCurrentUser();
?>

<div class="max-w-3xl mx-auto">
    <header class="mb-12">
        <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">My Profile</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">Manage your account and contact information.</p>
    </header>

    <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
        <div class="flex items-center gap-6 mb-8">
            <div class="w-24 h-24 bg-brutal-yellow border-4 border-black flex items-center justify-center text-4xl font-black shadow-brutal">
                <?= strtoupper(substr($user['first_name'], 0, 1)) . strtoupper(substr($user['last_name'], 0, 1)); ?>
            </div>
            <div>
                <h2 class="text-3xl font-black uppercase tracking-tighter"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p class="text-sm font-bold text-gray-500 uppercase"><?= $user['role']; ?> Account</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-gray-500">Student ID</label>
                <p class="text-lg font-bold p-3 border-2 border-black bg-gray-50"><?= htmlspecialchars($user['student_id']); ?></p>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-gray-500">Email Address</label>
                <p class="text-lg font-bold p-3 border-2 border-black bg-gray-50"><?= htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-gray-500">Phone Number</label>
                <p class="text-lg font-bold p-3 border-2 border-black bg-gray-50"><?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-gray-500">Account Status</label>
                <p class="text-lg font-bold p-3 border-2 border-black bg-green-100 text-green-800">Active</p>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t-4 border-black flex justify-end">
            <button class="px-6 py-2 border-2 border-black bg-white font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Update Information
            </button>
        </div>
    </div>
</div>

<?php require_once('layouts/student_footer.php'); ?>
