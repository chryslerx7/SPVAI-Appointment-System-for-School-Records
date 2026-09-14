<?php
// layouts/student_header.php
require_once __DIR__ . '/../class/Auth.php';
$auth->requireRole('student');
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brutal-bg min-h-screen font-sans text-black flex flex-col md:flex-row">

    <!-- Sidebar Navigation -->
    <nav class="w-full md:w-64 bg-white border-r-4 border-black flex flex-col">
        <div class="p-6 border-b-4 border-black">
            <h1 class="text-2xl font-black uppercase tracking-tighter">SPVAI</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-600">Student Portal</p>
        </div>

        <ul class="flex-1 p-4 space-y-2">
            <li>
                <a href="student_area.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="request_document.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    New Request
                </a>
            </li>
            <li>
                <a href="my_requests.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    My Requests
                </a>
            </li>
            <li>
                <a href="appointments.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    Appointments
                </a>
            </li>
            <li>
                <a href="payments.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    Payments
                </a>
            </li>
            <li>
                <a href="notifications.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    Notifications
                </a>
            </li>
            <li>
                <a href="profile.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
                    Profile
                </a>
            </li>
        </ul>

        <div class="p-4 border-t-4 border-black">
            <a href="logout.php" class="block p-3 border-2 border-black bg-red-500 text-white font-black uppercase text-sm text-center shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 md:p-12 overflow-y-auto">
