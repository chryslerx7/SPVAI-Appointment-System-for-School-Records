<?php
require_once('../class/Auth.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link class="icon" rel="icon" type="images/x-icon" href="images/spvai.ico">
    <title>Admin Login - SPVAI Records Office</title>
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
<body class="bg-brutal-bg min-h-screen flex items-center justify-center p-4 font-sans text-black">

    <div class="w-full max-w-md">
        <div class="bg-white border-4 border-black shadow-brutal-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black uppercase tracking-tighter mb-2">Admin Portal</h1>
                <p class="text-sm font-bold uppercase tracking-widest text-gray-600">SPVAI Records Office Management</p>
            </div>

            <form id="form-login" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken(); ?>">

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Username / Email</label>
                    <input type="text" id="un" name="un" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold" required placeholder="admin@spvai.edu.ph">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase">Password</label>
                    <input type="password" id="pwd" name="pwd" class="w-full border-2 border-black p-3 rounded-none focus:outline-none focus:ring-2 focus:ring-black font-bold" required placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-black text-white border-2 border-black py-3 font-black uppercase tracking-wide shadow-brutal hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                    Admin Login
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="../public_home.php" class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black transition-colors">← Return to Public Home</a>
        </div>
    </div>

    <script src="../assets/js/jquery-3.1.1.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
        $(document).on('submit', '#form-login', function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '../data/login.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function (data) {
                    if(data.valid == true){
                        window.location = data.url;
                    }else{
                        alert(data.msg);
                        $('#un').val("");
                        $('#pwd').val("");
                        $('#un').focus();
                    }
                },
                error: function(){
                    alert('Error: Login request failed.');
                }
            });
        });
    </script>
</body>
</html>
