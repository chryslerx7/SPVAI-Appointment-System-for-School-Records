<?php
require_once('class/Auth.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Register - SPVAI Records Office</title>
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
<body class="bg-brutal-bg min-h-screen flex items-center justify-center p-4 font-sans text-black py-12">

    <div class="w-full max-w-2xl">
        <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black uppercase tracking-tighter mb-2">Create Account</h1>
                <p class="text-sm font-bold uppercase tracking-widest text-gray-600">SPVAI Records Office Portal</p>
            </div>

            <form id="form-register" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                <div class="md:col-span-2">
                    <h2 class="text-lg font-black uppercase border-b-2 border-black mb-4 pb-1">Personal Information</h2>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Student ID</label>
                    <input type="text" name="student_id" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="2023-XXXXX">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Email Address</label>
                    <input type="email" name="email" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="student@spvai.edu.ph">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">First Name</label>
                    <input type="text" name="first_name" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="John">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Last Name</label>
                    <input type="text" name="last_name" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="Doe">
                </div>

                <div class="md:col-span-2">
                    <h2 class="text-lg font-black uppercase border-b-2 border-black mb-4 pb-1">Contact & Security</h2>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Phone Number</label>
                    <input type="text" name="phone" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" placeholder="09123456789">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Password</label>
                    <input type="password" name="password" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="••••••••">
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-xs font-black uppercase">Confirm Password</label>
                    <input type="password" name="confirm_password" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="••••••••">
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="w-full bg-brutal-yellow border-2 border-black py-3 font-black uppercase tracking-wide shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 active:shadow-none active:translate-x-0 active:translate-y-0 transition-all">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm font-medium">Already have an account?
                    <a href="login.php" class="font-black underline hover:text-brutal-yellow transition-colors">Login here</a>
                </p>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="public_home.php" class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black transition-colors">← Return to Home</a>
        </div>
    </div>

    <script src="assets/js/jquery-3.1.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
    $(document).on('submit', '#form-register', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'data/register.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(data) {
                if (data.valid) {
                    alert(data.msg);
                    window.location = 'login.php';
                } else {
                    alert(data.msg);
                }
            },
            error: function() {
                alert('An error occurred during registration. Please try again.');
            }
        });
    });
    </script>
</body>
</html>
