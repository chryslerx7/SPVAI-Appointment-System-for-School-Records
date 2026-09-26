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

    <!-- Mobile top bar with hamburger (tablet/mobile only, hidden on desktop) -->
    <div class="md:hidden w-full bg-white border-b-4 border-black flex items-center justify-between p-4 sticky top-0 z-30">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tighter">SPVAI</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-600">Student Portal</p>
        </div>
        <button id="student-menu-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="student-sidebar" class="p-3 border-2 border-black bg-brutal-yellow shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
            <span class="block w-6 h-1 bg-black mb-1" aria-hidden="true"></span>
            <span class="block w-6 h-1 bg-black mb-1" aria-hidden="true"></span>
            <span class="block w-6 h-1 bg-black" aria-hidden="true"></span>
        </button>
    </div>

    <!-- Backdrop for mobile sidebar (hidden on desktop) -->
    <div id="student-menu-backdrop" class="hidden fixed inset-0 z-30 bg-black bg-opacity-50 md:hidden" aria-hidden="true"></div>

    <!-- Sidebar Navigation: static sidebar on desktop, off-canvas panel on tablet/mobile -->
    <nav id="student-sidebar" aria-label="Student portal navigation" class="fixed inset-y-0 left-0 z-40 w-64 max-w-[85vw] bg-white border-r-4 border-black flex flex-col overflow-y-auto transform -translate-x-full transition-transform duration-200 md:static md:z-auto md:w-64 md:max-w-none md:shrink-0 md:overflow-visible md:translate-x-0 md:transition-none">
        <div class="p-6 border-b-4 border-black flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tighter">SPVAI</h1>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-600">Student Portal</p>
            </div>
            <button id="student-menu-close" type="button" aria-label="Close navigation menu" class="md:hidden px-3 py-1 border-2 border-black bg-white text-2xl font-black leading-none shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">&times;</button>
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
                <a href="my_requests.php" class="block p-3 border-2 border-transparent font-bold hover:border-black hover:bg-brutal-yellow transition-all uppercase text-sm">
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

        <div class="p-4 border-t-4 border-black mt-auto">
            <a href="logout.php" class="block p-3 border-2 border-black bg-red-500 text-white font-black uppercase text-sm text-center shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 md:p-12 overflow-y-auto">
