<?php
require_once('layouts/admin_header.php');

// 1. Fetch Statistics
// Pending Requests
$pendingReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Pending'");

// Approved Requests
$approvedReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Approved'");

// Rejected Requests
$rejectedReqs = $auth->getRow("SELECT COUNT(*) as count FROM requests WHERE status = 'Rejected'");

// Scheduled Appointments
$scheduledApps = $auth->getRow("SELECT COUNT(*) as count FROM appointments WHERE status = 'Scheduled'");

// Today's Appointments
$today = date('Y-m-d');
$todayApps = $auth->getRow("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND status != 'Cancelled'", [$today]);

// Pending Payments
$pendingPayments = $auth->getRow("SELECT COUNT(*) as count FROM payments WHERE payment_status = 'Pending Verification'");
?>

<div class="max-w-6xl mx-auto">
    <header class="mb-12">
        <h1 class="text-5xl font-black uppercase tracking-tighter mb-2">Office Overview</h1>
        <p class="text-lg font-bold text-gray-600 uppercase tracking-wide">System-wide records and request status.</p>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <div class="bg-white border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-500 mb-2">Pending Requests</p>
            <p class="text-5xl font-black"><?= $pendingReqs['count'] ?? 0; ?></p>
        </div>
        <div class="bg-white border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-500 mb-2">Approved Requests</p>
            <p class="text-5xl font-black"><?= $approvedReqs['count'] ?? 0; ?></p>
        </div>
        <div class="bg-white border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-500 mb-2">Rejected Requests</p>
            <p class="text-5xl font-black"><?= $rejectedReqs['count'] ?? 0; ?></p>
        </div>
        <div class="bg-brutal-yellow border-4 border-black shadow-brutal p-6 text-center">
            <p class="text-xs font-black uppercase text-gray-800 mb-2">Total Scheduled</p>
            <p class="text-5xl font-black"><?= $scheduledApps['count'] ?? 0; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Today's Appointments -->
        <div class="bg-white border-4 border-black shadow-brutal-lg p-8 flex flex-col items-center text-center">
            <h2 class="text-2xl font-black uppercase mb-6 border-b-4 border-black pb-2 w-full">Today's Appointments</h2>
            <div class="my-8">
                <span class="text-8xl font-black"><?= $todayApps['count'] ?? 0; ?></span>
                <p class="text-sm font-bold text-gray-500 uppercase mt-2">Scheduled for Today</p>
            </div>
            <a href="appointments.php" class="px-8 py-3 bg-black text-white border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                View Schedule
            </a>
        </div>

        <!-- Payment Verification -->
        <div class="bg-white border-4 border-black shadow-brutal-lg p-8 flex flex-col items-center text-center">
            <h2 class="text-2xl font-black uppercase mb-6 border-b-4 border-black pb-2 w-full">Payment Verification</h2>
            <div class="my-8">
                <span class="text-8xl font-black text-red-600"><?= $pendingPayments['count'] ?? 0; ?></span>
                <p class="text-sm font-bold text-gray-500 uppercase mt-2">Awaiting Verification</p>
            </div>
            <a href="payments.php" class="px-8 py-3 bg-red-500 text-white border-2 border-black font-black uppercase text-sm shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Verify Now
            </a>
        </div>
    </div>
</div>

<?php require_once('layouts/admin_footer.php'); ?>
