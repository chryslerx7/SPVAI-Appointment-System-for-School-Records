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
    <title>Login - SPVAI Records Office</title>
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
    <style>
        .btn-brutal {
            @apply px-4 py-2 border-2 border-black font-bold transition-all shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 active:shadow-none active:translate-x-0 active:translate-y-0;
        }
        /* Since we are using CDN, we can't use @apply in <style> unless we use custom classes */
    </style>
</head>
<body class="bg-brutal-bg min-h-screen flex items-center justify-center p-4 font-sans text-black">

    <div class="w-full max-w-md">
        <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black uppercase tracking-tighter mb-2">SPVAI</h1>
                <p class="text-sm font-bold uppercase tracking-widest text-gray-600">Records Office Portal</p>
            </div>

            <form id="form-login" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                <div>
                    <label class="block text-xs font-black uppercase mb-1">Email Address</label>
                    <input type="email" name="email" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="student@spvai.edu.ph">
                </div>

                <div>
                    <label class="block text-xs font-black uppercase mb-1">Password</label>
                    <input type="password" name="password" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black" required placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-brutal-yellow border-2 border-black py-3 font-black uppercase tracking-wide shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 active:shadow-none active:translate-x-0 active:translate-y-0 transition-all">
                    Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm font-medium">Don't have an account?
                    <a href="register.php" class="font-black underline hover:text-brutal-yellow transition-colors">Register here</a>
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
    $(document).on('submit', '#form-login', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'data/auth_login.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(data) {
                if (data.valid) {
                    alert(data.msg);
                    window.location = data.url;
                } else {
                    alert(data.msg);
                }
            },
            error: function() {
                alert('An error occurred during login. Please try again.');
            }
        });
    });
    </script>
</body>
</html>
