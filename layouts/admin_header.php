<?php
// layouts/admin_header.php
require_once __DIR__ . '/../class/Auth.php';
$auth->requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        'brutal': '4px 4px 0px 0px rgba(0,0,0,1)',
                        'brutal-lg': '8px 8px 0px 0px rgba(0,0,0,1)',
                    },
                    colors: {
                        'brutal-yellow': '#FACC15',
                        'brutal-bg': '#F9F9F9',
                        'admin-dark': '#1A1A1A',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brutal-bg min-h-screen font-sans text-black flex flex-col md:flex-row">

    <!-- Admin Sidebar Navigation -->
    <nav class="w-full md:w-64 bg-admin-dark text-white flex flex-col border-r-4 border-black">
        <div class="p-6 border-b-4 border-black bg-black">
            <h1 class="text-2xl font-black uppercase tracking-tighter text-white">SPVAI Admin</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Records Office Mgmt</p>
        </div>

        <ul class="flex-1 p-4 space-y-2">
            <li class="group">
                <a href="dashboard.php" class="block p-3 border-2 border-transparent font-bold hover:border-white hover:bg-white hover:text-black transition-all uppercase text-sm">
                    Dashboard
                </a>
            </li>
            <li class="group">
                <a href="requests.php" class="block p-3 border-2 border-transparent font-bold hover:border-white hover:bg-white hover:text-black transition-all uppercase text-sm">
                    Manage Requests
                </a>
            </li>
            <li class="group">
                <a href="appointments.php" class="block p-3 border-2 border-transparent font-bold hover:border-white hover:bg-white hover:text-black transition-all uppercase text-sm">
                    Appointments
                </a>
            </li>
            <li class="group">
                <a href="payments.php" class="block p-3 border-2 border-transparent font-bold hover:border-white hover:bg-white hover:text-black transition-all uppercase text-sm">
                    Verify Payments
                </a>
            </li>
        </ul>

        <div class="p-4 border-t-4 border-black bg-black">
            <a href="../logout.php" class="block p-3 border-2 border-white bg-red-600 text-white font-black uppercase text-sm text-center shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 md:p-12 overflow-y-auto">
