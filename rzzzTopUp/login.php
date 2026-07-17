<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - rzzzTopUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); }
        .btn-gradient { background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); }
        .btn-gradient:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed, #8b5cf6); }
        .hero-gradient { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); }
    </style>
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl btn-gradient flex items-center justify-center font-extrabold text-2xl mx-auto mb-3">R</div>
            <h1 class="text-2xl font-extrabold text-white">rzzz<span class="text-indigo-400">TopUp</span></h1>
            <p class="text-gray-400 text-sm mt-1">Admin Dashboard Login</p>
        </div>

        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold text-white mb-6 text-center">Masuk ke Dashboard</h2>

            <form id="loginForm" autocomplete="off">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Username</label>
                    <input type="text" id="username" placeholder="Masukkan username"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition placeholder-gray-600">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Password</label>
                    <input type="password" id="password" placeholder="Masukkan password"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition placeholder-gray-600">
                </div>
                <button type="submit" id="btnLogin"
                    class="w-full btn-gradient text-white font-bold py-3 rounded-xl text-sm hover:opacity-90 transition">
                    Masuk
                </button>
            </form>

            <div id="loginMsg" class="mt-4 text-center text-sm hidden"></div>
        </div>

        <p class="text-center text-gray-600 text-xs mt-6">&copy; 2026 rzzzTopUp</p>
    </div>

<script>
$(function(){
    $('#loginForm').on('submit', function(e){
        e.preventDefault();

        let user = $('#username').val().trim();
        let pass = $('#password').val().trim();

        if(!user || !pass){
            showMsg('Isi semua field.', 'error');
            return;
        }

        $('#btnLogin').prop('disabled', true).text('Memproses...');

        $.ajax({
            url: 'proses_login.php',
            method: 'POST',
            dataType: 'json',
            data: { username: user, password: pass },
            success: function(res){
                if(res.success){
                    showMsg('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(function(){ window.location.href = 'admin_dashboard.php'; }, 1000);
                } else {
                    showMsg(res.message || 'Login gagal.', 'error');
                    $('#btnLogin').prop('disabled', false).text('Masuk');
                }
            },
            error: function(){
                showMsg('Gagal menghubungi server.', 'error');
                $('#btnLogin').prop('disabled', false).text('Masuk');
            }
        });
    });

    function showMsg(msg, type){
        let cls = type === 'success' ? 'text-green-400' : 'text-red-400';
        $('#loginMsg').removeClass('hidden').addClass(cls).text(msg);
        setTimeout(function(){ $('#loginMsg').addClass('hidden').removeClass(cls); }, 4000);
    }
});
</script>
</body>
</html>
