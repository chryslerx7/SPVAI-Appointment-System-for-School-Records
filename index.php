<?php
require_once('class/Auth.php');

// Determine routing based on authentication status
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();

    if (!$user) {
        // Session exists but user record not found (e.g. deleted user)
        $auth->logout();
        header("Location: login.php");
        exit();
    }

    if ($user['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: student_area.php");
    }
    exit();
}
// Not logged in: render the public landing page below.
// Authenticated routing above is intentionally untouched.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>SPVAI — School Records Office Appointment &amp; Document Request System</title>
    <meta name="description" content="SPVAI Records Office portal: request school documents, schedule appointments, monitor request and payment status, and receive notifications.">
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
<body class="bg-brutal-bg min-h-screen font-sans text-black">

    <!-- Top bar -->
    <header class="bg-white border-b-4 border-black">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-2xl font-black uppercase tracking-tighter leading-none">SPVAI</p>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-600">Records Office Portal</p>
            </div>
            <nav aria-label="Account access" class="flex flex-wrap gap-3">
                <a href="login.php" class="px-5 py-2 border-2 border-black bg-white font-black uppercase text-xs shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Student Login
                </a>
                <a href="register.php" class="px-5 py-2 border-2 border-black bg-brutal-yellow font-black uppercase text-xs shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Register
                </a>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4">
        <!-- Hero -->
        <section class="py-12 md:py-20 text-center">
            <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tighter mb-4">SPVAI</h1>
            <p class="text-lg md:text-2xl font-bold uppercase tracking-wide mb-6">School Records Office Appointment &amp; Document Request System</p>
            <p class="max-w-2xl mx-auto text-base md:text-lg font-medium text-gray-700 mb-10">
                Request school documents, schedule Records Office appointments, monitor your
                request status, track payments, and receive notifications — all in one portal.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="login.php" class="px-10 py-4 bg-brutal-yellow border-4 border-black font-black uppercase tracking-wide shadow-brutal-lg hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Get Started
                </a>
                <a href="register.php" class="px-10 py-4 bg-white border-4 border-black font-black uppercase tracking-wide shadow-brutal-lg hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Create Student Account
                </a>
            </div>
        </section>

        <!-- Features -->
        <section aria-label="What you can do" class="pb-12 md:pb-20">
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter mb-8 text-center">What You Can Do</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <div class="bg-white border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">Request Documents</h3>
                    <p class="text-sm font-medium text-gray-700">Submit official school document requests online with a clear reference number.</p>
                </div>
                <div class="bg-white border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">Schedule Appointments</h3>
                    <p class="text-sm font-medium text-gray-700">Pick an available Records Office visit date and time for your request.</p>
                </div>
                <div class="bg-white border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">Monitor Status</h3>
                    <p class="text-sm font-medium text-gray-700">Follow each request from submission through approval to completion.</p>
                </div>
                <div class="bg-white border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">Track Payments</h3>
                    <p class="text-sm font-medium text-gray-700">Submit payment references and watch verification status update.</p>
                </div>
                <div class="bg-white border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">Get Notified</h3>
                    <p class="text-sm font-medium text-gray-700">Receive in-portal notifications whenever your request status changes.</p>
                </div>
                <div class="bg-brutal-yellow border-4 border-black shadow-brutal p-6">
                    <h3 class="text-lg font-black uppercase mb-2">New Here?</h3>
                    <p class="text-sm font-bold mb-4">Registration is for students and takes less than a minute.</p>
                    <a href="register.php" class="inline-block px-5 py-2 bg-black text-white border-2 border-black font-black uppercase text-xs shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                        Register Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Steps -->
        <section aria-label="How it works" class="pb-12 md:pb-20">
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter mb-8 text-center">How It Works</h2>
            <ol class="grid grid-cols-1 md:grid-cols-4 gap-6 md:gap-8 list-none">
                <li class="bg-white border-4 border-black shadow-brutal p-6">
                    <p class="text-4xl font-black mb-2">1</p>
                    <h3 class="font-black uppercase mb-1">Register &amp; Login</h3>
                    <p class="text-sm font-medium text-gray-700">Create your student account and sign in.</p>
                </li>
                <li class="bg-white border-4 border-black shadow-brutal p-6">
                    <p class="text-4xl font-black mb-2">2</p>
                    <h3 class="font-black uppercase mb-1">Request</h3>
                    <p class="text-sm font-medium text-gray-700">Choose a document and submit your request.</p>
                </li>
                <li class="bg-white border-4 border-black shadow-brutal p-6">
                    <p class="text-4xl font-black mb-2">3</p>
                    <h3 class="font-black uppercase mb-1">Schedule &amp; Pay</h3>
                    <p class="text-sm font-medium text-gray-700">Book your visit and submit payment details.</p>
                </li>
                <li class="bg-white border-4 border-black shadow-brutal p-6">
                    <p class="text-4xl font-black mb-2">4</p>
                    <h3 class="font-black uppercase mb-1">Track</h3>
                    <p class="text-sm font-medium text-gray-700">Follow status updates and notifications.</p>
                </li>
            </ol>
        </section>
    </main>

    <footer class="border-t-4 border-black bg-white">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-600">SPVAI Records Office Portal</p>
            <a href="admin/index.php" class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black transition-colors">Admin Login</a>
        </div>
    </footer>

</body>
</html>
