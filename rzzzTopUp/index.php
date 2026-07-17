<?php require 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>rzzzTopUp - Top Up Game Termurah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); }
        .game-card { transition: all 0.3s ease; }
        .game-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(99,102,241,0.3); }
        .game-card.selected { border-color: #818cf8; box-shadow: 0 0 0 3px rgba(129,140,248,0.5); }
        .nominal-btn { transition: all 0.2s ease; }
        .nominal-btn:hover { transform: scale(1.05); }
        .nominal-btn.selected { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border-color: transparent; }
        .pay-btn { transition: all 0.2s ease; }
        .pay-btn:hover { transform: scale(1.05); }
        .pay-btn.selected { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border-color: transparent; }
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.6; } }
        .hero-gradient { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); }
        .btn-gradient { background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); }
        .btn-gradient:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed, #8b5cf6); }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.35s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: all; }

        .modal-box {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.5rem; padding: 3rem 2.5rem;
            text-align: center; width: 380px; max-width: 92vw;
            transform: scale(0.85); transition: transform 0.35s ease;
        }
        .modal-overlay.active .modal-box { transform: scale(1); }

        /* Spinner */
        .spinner-wrap { width: 90px; height: 90px; margin: 0 auto 1.5rem; position: relative; }
        .spinner-ring {
            position: absolute; inset: 0; border-radius: 50%;
            border: 4px solid transparent;
        }
        .spinner-ring:nth-child(1) { border-top-color: #818cf8; animation: spin 1s linear infinite; }
        .spinner-ring:nth-child(2) { inset: 8px; border-right-color: #a78bfa; animation: spin 1.4s linear infinite reverse; }
        .spinner-ring:nth-child(3) { inset: 16px; border-bottom-color: #c4b5fd; animation: spin 1.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Success Check */
        .success-circle {
            width: 90px; height: 90px; margin: 0 auto 1.5rem;
            border-radius: 50%; background: linear-gradient(135deg, #10b981, #34d399);
            display: flex; align-items: center; justify-content: center;
            transform: scale(0); transition: transform 0.4s cubic-bezier(.175,.885,.32,1.275);
        }
        .modal-overlay.active .success-circle.show { transform: scale(1); }
        .success-check {
            width: 36px; height: 20px;
            border-left: 4px solid #fff; border-bottom: 4px solid #fff;
            transform: rotate(-45deg) translate(2px, -2px);
            opacity: 0; transition: opacity 0.3s ease 0.25s;
        }
        .modal-overlay.active .success-check.show { opacity: 1; }

        /* Failed Cross */
        .failed-circle {
            width: 90px; height: 90px; margin: 0 auto 1.5rem;
            border-radius: 50%; background: linear-gradient(135deg, #ef4444, #f87171);
            display: flex; align-items: center; justify-content: center;
            transform: scale(0); transition: transform 0.4s cubic-bezier(.175,.885,.32,1.275);
        }
        .modal-overlay.active .failed-circle.show { transform: scale(1); }
        .failed-x {
            position: relative; width: 30px; height: 30px; opacity: 0;
            transition: opacity 0.3s ease 0.25s;
        }
        .modal-overlay.active .failed-x.show { opacity: 1; }
        .failed-x::before, .failed-x::after {
            content: ''; position: absolute; top: 50%; left: 50%;
            width: 32px; height: 4px; background: #fff; border-radius: 2px;
        }
        .failed-x::before { transform: translate(-50%, -50%) rotate(45deg); }
        .failed-x::after { transform: translate(-50%, -50%) rotate(-45deg); }

        .modal-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; }
        .modal-desc { font-size: 0.8rem; color: #9ca3af; margin-bottom: 1.5rem; line-height: 1.6; }
        .modal-btn {
            display: inline-block; padding: 0.7rem 2.5rem; border-radius: 0.75rem;
            font-size: 0.85rem; font-weight: 700; cursor: pointer;
            border: none; transition: all 0.2s;
        }
        .modal-btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
        .modal-btn-primary:hover { opacity: 0.85; }
    </style>
</head>
<body class="hero-gradient min-h-screen text-white">

    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl btn-gradient flex items-center justify-center font-extrabold text-lg">R</div>
                <span class="text-xl font-extrabold tracking-tight">rzzz<span class="text-indigo-400">TopUp</span></span>
            </div>
            <a href="login.php" class="text-sm text-indigo-300 hover:text-white transition">Admin Login</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-4 pt-10 pb-6 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold mb-3 leading-tight">
            Top Up Game <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Mudah & Cepat</span>
        </h1>
        <p class="text-gray-400 text-sm md:text-base max-w-lg mx-auto">Pilih game, masukkan ID, pilih nominal, dan bayar. Proses otomatis & instan!</p>
    </section>

    <main class="max-w-6xl mx-auto px-4 pb-16">

        <!-- Step 1: Pilih Game -->
        <section id="step1" class="mb-8">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full btn-gradient flex items-center justify-center text-sm">1</span>
                Pilih Game
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="game-card glass rounded-2xl p-6 cursor-pointer text-center" data-game="Mobile Legends">
                    <div class="text-5xl mb-3">⚔️</div>
                    <h3 class="font-bold text-sm">Mobile Legends</h3>
                    <p class="text-xs text-gray-400 mt-1">Diamond & Starlight</p>
                </div>
                <div class="game-card glass rounded-2xl p-6 cursor-pointer text-center" data-game="Free Fire">
                    <div class="text-5xl mb-3">🔥</div>
                    <h3 class="font-bold text-sm">Free Fire</h3>
                    <p class="text-xs text-gray-400 mt-1">Diamond & Membership</p>
                </div>
                <div class="game-card glass rounded-2xl p-6 cursor-pointer text-center opacity-40 cursor-not-allowed">
                    <div class="text-5xl mb-3">🎯</div>
                    <h3 class="font-bold text-sm">Valorant</h3>
                    <p class="text-xs text-gray-400 mt-1">Segera Hadir</p>
                </div>
                <div class="game-card glass rounded-2xl p-6 cursor-pointer text-center opacity-40 cursor-not-allowed">
                    <div class="text-5xl mb-3">🎮</div>
                    <h3 class="font-bold text-sm">Genshin Impact</h3>
                    <p class="text-xs text-gray-400 mt-1">Segera Hadir</p>
                </div>
            </div>
        </section>

        <!-- Step 2: Form Input ID (Hidden by default) -->
        <section id="step2" class="mb-8 hidden">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full btn-gradient flex items-center justify-center text-sm">2</span>
                Masukkan ID Game
            </h2>
            <div class="glass rounded-2xl p-6 fade-in">
                <div class="flex items-center gap-3 mb-4">
                    <span id="selectedGameLabel" class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300"></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">User ID / ID Game</label>
                        <input type="text" id="userId" placeholder="Contoh: 12345678"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-gray-600">
                    </div>
                    <div id="zoneIdGroup">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Zone ID</label>
                        <input type="text" id="zoneId" placeholder="Contoh: 2045"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition placeholder-gray-600">
                    </div>
                </div>
            </div>
        </section>

        <!-- Step 3: Pilih Nominal -->
        <section id="step3" class="mb-8 hidden">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full btn-gradient flex items-center justify-center text-sm">3</span>
                Pilih Nominal
            </h2>
            <div id="nominalGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 fade-in"></div>
        </section>

        <!-- Step 4: Metode Pembayaran -->
        <section id="step4" class="mb-8 hidden">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full btn-gradient flex items-center justify-center text-sm">4</span>
                Metode Pembayaran
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 fade-in">
                <div class="pay-btn glass rounded-xl p-4 cursor-pointer text-center border-2 border-transparent" data-pay="DANA">
                    <div class="text-2xl mb-1">💙</div>
                    <span class="text-xs font-semibold">DANA</span>
                </div>
                <div class="pay-btn glass rounded-xl p-4 cursor-pointer text-center border-2 border-transparent" data-pay="GoPay">
                    <div class="text-2xl mb-1">💚</div>
                    <span class="text-xs font-semibold">GoPay</span>
                </div>
                <div class="pay-btn glass rounded-xl p-4 cursor-pointer text-center border-2 border-transparent" data-pay="OVO">
                    <div class="text-2xl mb-1">💜</div>
                    <span class="text-xs font-semibold">OVO</span>
                </div>
                <div class="pay-btn glass rounded-xl p-4 cursor-pointer text-center border-2 border-transparent" data-pay="Transfer Bank">
                    <div class="text-2xl mb-1">🏦</div>
                    <span class="text-xs font-semibold">Transfer Bank</span>
                </div>
            </div>
        </section>

        <!-- Ringkasan & Beli -->
        <section id="step5" class="mb-8 hidden">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full btn-gradient flex items-center justify-center text-sm">5</span>
                Ringkasan & Beli
            </h2>
            <div class="glass rounded-2xl p-6 fade-in">
                <div class="space-y-2 mb-5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Game</span><span id="sumGame" class="font-semibold">-</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">User ID</span><span id="sumUserId" class="font-semibold">-</span></div>
                    <div id="sumZoneRow" class="flex justify-between hidden"><span class="text-gray-400">Zone ID</span><span id="sumZone" class="font-semibold">-</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Nominal</span><span id="sumNominal" class="font-semibold">-</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Pembayaran</span><span id="sumPay" class="font-semibold">-</span></div>
                    <hr class="border-white/10">
                    <div class="flex justify-between text-base"><span class="text-gray-400">Harga</span><span id="sumHarga" class="font-bold text-indigo-400">-</span></div>
                </div>
                <button id="btnBeli" class="w-full btn-gradient text-white font-bold py-3 rounded-xl text-sm hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    Beli Sekarang
                </button>
                <p id="notifBox" class="mt-3 text-center text-sm hidden"></p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="glass py-6 text-center text-xs text-gray-500">
        &copy; 2026 rzzzTopUp. Top up game favoritmu sekarang!
    </footer>

    <!-- Modal Transaksi -->
    <div id="txModal" class="modal-overlay">
        <div class="modal-box">
            <!-- State: Processing -->
            <div id="stateLoading">
                <div class="spinner-wrap">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                </div>
                <div class="modal-title text-indigo-300">Memproses Transaksi</div>
                <div class="modal-desc">Mohon tunggu sebentar, sedang memverifikasi data dan memproses pembayaran Anda...</div>
                <div class="w-full bg-white/10 rounded-full h-1.5 mb-2">
                    <div id="progressBar" class="btn-gradient h-1.5 rounded-full transition-all duration-700" style="width:0%"></div>
                </div>
                <span class="text-xs text-gray-500 pulse">Jangan tutup halaman ini</span>
            </div>

            <!-- State: Success -->
            <div id="stateSuccess" class="hidden">
                <div class="success-circle">
                    <div class="success-check"></div>
                </div>
                <div class="modal-title text-green-400">Transaksi Berhasil!</div>
                <div class="modal-desc" id="successDesc">ID transaksi Anda telah tercatat. Silakan cek status di admin dashboard.</div>
                <button class="modal-btn modal-btn-primary" onclick="closeModal()">Lanjutkan</button>
            </div>

            <!-- State: Failed -->
            <div id="stateFailed" class="hidden">
                <div class="failed-circle">
                    <div class="failed-x"></div>
                </div>
                <div class="modal-title text-red-400">Transaksi Gagal</div>
                <div class="modal-desc" id="failedDesc">Terjadi kesalahan. Silakan coba lagi atau hubungi admin.</div>
                <button class="modal-btn modal-btn-primary" onclick="closeModal()">Coba Lagi</button>
            </div>
        </div>
    </div>

<script>
$(function(){
    let selectedGame = null;
    let selectedNominal = null;
    let selectedHarga = null;
    let selectedPay = null;

    /* ---- Modal helpers ---- */
    window.closeModal = function(){
        $('#txModal').removeClass('active');
        $('#stateLoading, #stateSuccess, #stateFailed').addClass('hidden');
        $('.success-circle, .success-check, .failed-circle, .failed-x').removeClass('show');
        $('#progressBar').css('width','0%');
    };

    function showModal(){
        $('#stateLoading').removeClass('hidden');
        $('#stateSuccess, #stateFailed').addClass('hidden');
        $('.success-circle, .success-check, .failed-circle, .failed-x').removeClass('show');
        $('#txModal').addClass('active');
        animateProgress();
    }

    function showSuccess(id){
        $('#stateLoading').addClass('hidden');
        $('#stateSuccess').removeClass('hidden');
        $('#successDesc').html('Transaksi <strong>#'+id+'</strong> berhasil dibuat.<br>Status: <strong class="text-yellow-400">Pending</strong> — menunggu verifikasi admin.');
        setTimeout(function(){
            $('.success-circle').addClass('show');
            setTimeout(function(){ $('.success-check').addClass('show'); }, 200);
        }, 50);
    }

    function showFailed(msg){
        $('#stateLoading').addClass('hidden');
        $('#stateFailed').removeClass('hidden');
        $('#failedDesc').text(msg);
        setTimeout(function(){
            $('.failed-circle').addClass('show');
            setTimeout(function(){ $('.failed-x').addClass('show'); }, 200);
        }, 50);
    }

    /* ---- Fake progress bar ---- */
    let progressInterval = null;
    function animateProgress(){
        let bar = $('#progressBar');
        bar.css('width','0%');
        let pct = 0;
        clearInterval(progressInterval);
        progressInterval = setInterval(function(){
            if(pct < 60) pct += Math.random() * 8;
            else if(pct < 85) pct += Math.random() * 3;
            else if(pct < 95) pct += Math.random() * 1;
            if(pct > 95) pct = 95;
            bar.css('width', pct + '%');
        }, 200);
    }

    function finishProgress(){
        clearInterval(progressInterval);
        $('#progressBar').css('width','100%');
    }

    /* ---- Load nominal dari server ---- */
    function loadNominal(game){
        $.ajax({
            url: 'api_nominal.php',
            method: 'GET',
            data: { game: game },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    let html = '';
                    res.data.forEach(function(item){
                        html += '<div class="nominal-btn glass rounded-xl p-4 cursor-pointer text-center border-2 border-transparent" data-id="'+item.id+'" data-nominal="'+item.nominal+'" data-harga="'+item.harga+'">';
                        html += '<div class="font-bold text-sm">'+item.nominal+'</div>';
                        html += '<div class="text-indigo-400 text-xs mt-1 font-semibold">Rp '+Number(item.harga).toLocaleString('id-ID')+'</div>';
                        html += '</div>';
                    });
                    $('#nominalGrid').html(html);
                }
            }
        });
    }

    /* ---- Step 1: Pilih Game ---- */
    $('.game-card[data-game]').on('click', function(){
        $('.game-card').removeClass('selected');
        $(this).addClass('selected');
        selectedGame = $(this).data('game');
        selectedNominal = null;
        selectedHarga = null;

        $('#selectedGameLabel').text(selectedGame);
        $('#step2').removeClass('hidden');
        $('#step3').removeClass('hidden');
        $('#step4').addClass('hidden');
        $('#step5').addClass('hidden');

        if(selectedGame === 'Mobile Legends'){
            $('#zoneIdGroup').show();
        } else {
            $('#zoneIdGroup').hide();
            $('#zoneId').val('');
        }

        loadNominal(selectedGame);
    });

    /* ---- Step 3: Pilih Nominal ---- */
    $(document).on('click', '.nominal-btn', function(){
        $('.nominal-btn').removeClass('selected');
        $(this).addClass('selected');
        selectedNominal = $(this).data('nominal');
        selectedHarga = $(this).data('harga');
        $('#step4').removeClass('hidden');
        $('#step5').addClass('hidden');
        checkSummary();
    });

    /* ---- Step 4: Pilih Pembayaran ---- */
    $(document).on('click', '.pay-btn', function(){
        $('.pay-btn').removeClass('selected');
        $(this).addClass('selected');
        selectedPay = $(this).data('pay');
        $('#step5').removeClass('hidden');
        checkSummary();
    });

    /* ---- Ringkasan ---- */
    function checkSummary(){
        let uid = $('#userId').val().trim();
        let zid = $('#zoneId').val().trim();

        $('#sumGame').text(selectedGame || '-');
        $('#sumUserId').text(uid || '-');

        if(selectedGame === 'Mobile Legends'){
            $('#sumZoneRow').removeClass('hidden');
            $('#sumZone').text(zid || '-');
        } else {
            $('#sumZoneRow').addClass('hidden');
        }

        $('#sumNominal').text(selectedNominal || '-');
        $('#sumPay').text(selectedPay || '-');
        $('#sumHarga').text(selectedHarga ? 'Rp '+Number(selectedHarga).toLocaleString('id-ID') : '-');

        if(selectedGame && uid && selectedNominal && selectedPay){
            $('#btnBeli').prop('disabled', false);
        } else {
            $('#btnBeli').prop('disabled', true);
        }
    }

    $(document).on('input', '#userId, #zoneId', function(){
        checkSummary();
    });

    /* ---- Step 5: Beli Sekarang ---- */
    $('#btnBeli').on('click', function(){
        let uid = $('#userId').val().trim();
        let zid = $('#zoneId').val().trim();

        if(!selectedGame || !uid || !selectedNominal || !selectedPay){
            return;
        }

        if(selectedGame === 'Mobile Legends' && !zid){
            showNotif('Zone ID wajib diisi untuk Mobile Legends!', 'error');
            return;
        }

        $(this).prop('disabled', true);

        /* Tampilkan modal proses */
        showModal();

        $.ajax({
            url: 'proses_transaksi.php',
            method: 'POST',
            dataType: 'json',
            data: {
                user_id_game: uid,
                zone_id: zid,
                game_type: selectedGame,
                nominal: selectedNominal,
                metode_pembayaran: selectedPay
            },
            success: function(res){
                finishProgress();
                setTimeout(function(){
                    if(res.success){
                        showSuccess(res.id);
                        /* Reset form */
                        $('#userId').val('');
                        $('#zoneId').val('');
                        selectedNominal = null;
                        selectedHarga = null;
                        selectedPay = null;
                        $('.nominal-btn, .pay-btn').removeClass('selected');
                        $('#step4').addClass('hidden');
                        $('#step5').addClass('hidden');
                    } else {
                        showFailed(res.message || 'Terjadi kesalahan saat memproses transaksi.');
                    }
                    $('#btnBeli').prop('disabled', false);
                    checkSummary();
                }, 600);
            },
            error: function(){
                finishProgress();
                setTimeout(function(){
                    showFailed('Gagal menghubungi server. Periksa koneksi internet Anda.');
                    $('#btnBeli').prop('disabled', false);
                    checkSummary();
                }, 600);
            }
        });
    });

    function showNotif(msg, type){
        let cls = type === 'success' ? 'text-green-400' : 'text-red-400';
        $('#notifBox').removeClass('hidden').addClass(cls).text(msg);
        setTimeout(function(){ $('#notifBox').addClass('hidden').removeClass(cls); }, 5000);
    }
});
</script>
</body>
</html>
