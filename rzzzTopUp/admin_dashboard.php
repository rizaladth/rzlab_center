<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require 'koneksi.php';

// Ambil semua transaksi
$stmt = $pdo->query("SELECT * FROM transaksi ORDER BY tanggal DESC");
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - rzzzTopUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); }
        .btn-gradient { background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); }
        .btn-gradient:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed, #8b5cf6); }
        .hero-gradient { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); }
        .status-pending { background: #f59e0b20; color: #fbbf24; }
        .status-sukses  { background: #10b98120; color: #34d399; }
        .status-gagal   { background: #ef444420; color: #f87171; }
        .toast { position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 14px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; transform: translateX(120%); transition: transform 0.4s ease; }
        .toast.show { transform: translateX(0); }
    </style>
</head>
<body class="hero-gradient min-h-screen text-white">

    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl btn-gradient flex items-center justify-center font-extrabold text-lg">R</div>
                <span class="text-xl font-extrabold tracking-tight">rzzz<span class="text-indigo-400">TopUp</span></span>
                <span class="ml-2 text-xs bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full font-semibold">Admin</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400">Halo, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></span>
                <a href="logout.php" class="text-sm text-red-400 hover:text-red-300 transition">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Stats -->
    <div class="max-w-7xl mx-auto px-4 pt-8">
        <h1 class="text-2xl font-extrabold mb-6">Dashboard Transaksi</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="glass rounded-2xl p-5">
                <p class="text-gray-400 text-xs font-semibold mb-1">Total Transaksi</p>
                <p id="statTotal" class="text-3xl font-extrabold">0</p>
            </div>
            <div class="glass rounded-2xl p-5">
                <p class="text-gray-400 text-xs font-semibold mb-1">Pending</p>
                <p id="statPending" class="text-3xl font-extrabold text-yellow-400">0</p>
            </div>
            <div class="glass rounded-2xl p-5">
                <p class="text-gray-400 text-xs font-semibold mb-1">Sukses</p>
                <p id="statSukses" class="text-3xl font-extrabold text-green-400">0</p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="max-w-7xl mx-auto px-4 mb-4">
        <div class="flex flex-wrap gap-2">
            <button class="filter-btn px-4 py-2 rounded-xl text-xs font-semibold glass border-2 border-indigo-500 text-white" data-filter="Semua">Semua</button>
            <button class="filter-btn px-4 py-2 rounded-xl text-xs font-semibold glass border-2 border-transparent text-gray-400 hover:text-white transition" data-filter="Pending">Pending</button>
            <button class="filter-btn px-4 py-2 rounded-xl text-xs font-semibold glass border-2 border-transparent text-gray-400 hover:text-white transition" data-filter="Sukses">Sukses</button>
            <button class="filter-btn px-4 py-2 rounded-xl text-xs font-semibold glass border-2 border-transparent text-gray-400 hover:text-white transition" data-filter="Gagal">Gagal</button>
        </div>
    </div>

    <!-- Tabel -->
    <div class="max-w-7xl mx-auto px-4 pb-16">
        <div class="glass rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">#</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Tanggal</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">User ID</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Zone ID</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Game</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Nominal</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Pembayaran</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Status</th>
                            <th class="px-5 py-4 font-semibold text-gray-400 text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-gray-500">Belum ada transaksi.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $i => $trx): ?>
                            <tr class="border-b border-white/5 hover:bg-white/5 transition trx-row" data-status="<?php echo $trx['status']; ?>">
                                <td class="px-5 py-4 text-gray-500"><?php echo $i + 1; ?></td>
                                <td class="px-5 py-4 text-xs text-gray-400"><?php echo date('d M Y H:i', strtotime($trx['tanggal'])); ?></td>
                                <td class="px-5 py-4 font-mono text-xs"><?php echo htmlspecialchars($trx['user_id_game']); ?></td>
                                <td class="px-5 py-4 text-xs"><?php echo $trx['zone_id'] ? htmlspecialchars($trx['zone_id']) : '-'; ?></td>
                                <td class="px-5 py-4 text-xs"><?php echo htmlspecialchars($trx['game_type']); ?></td>
                                <td class="px-5 py-4 text-xs"><?php echo htmlspecialchars($trx['nominal']); ?></td>
                                <td class="px-5 py-4 text-xs"><?php echo htmlspecialchars($trx['metode_pembayaran']); ?></td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold status-<?php echo strtolower($trx['status']); ?>"><?php echo $trx['status']; ?></span>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if ($trx['status'] === 'Pending'): ?>
                                    <div class="flex gap-1">
                                        <button class="btn-status px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-500/20 text-green-400 hover:bg-green-500/30 transition"
                                            data-id="<?php echo $trx['id']; ?>" data-action="Sukses">Sukses</button>
                                        <button class="btn-status px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-500/20 text-red-400 hover:bg-red-500/30 transition"
                                            data-id="<?php echo $trx['id']; ?>" data-action="Gagal">Gagal</button>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-600">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

<script>
$(function(){
    updateStats();

    /* ---- Filter ---- */
    $(document).on('click', '.filter-btn', function(){
        let filter = $(this).data('filter');
        $('.filter-btn').removeClass('border-indigo-500 text-white').addClass('border-transparent text-gray-400');
        $(this).removeClass('border-transparent text-gray-400').addClass('border-indigo-500 text-white');

        if(filter === 'Semua'){
            $('.trx-row').show();
        } else {
            $('.trx-row').hide();
            $('.trx-row[data-status="'+filter+'"]').show();
        }
    });

    /* ---- Update Status ---- */
    $(document).on('click', '.btn-status', function(){
        let btn = $(this);
        let id  = btn.data('id');
        let action = btn.data('action');

        btn.prop('disabled', true).text('...');

        $.ajax({
            url: 'proses_status.php',
            method: 'POST',
            dataType: 'json',
            data: { id: id, status: action },
            success: function(res){
                if(res.success){
                    showToast('Transaksi #' + id + ' diubah ke ' + action, 'success');
                    // Update badge
                    let row = btn.closest('tr');
                    let badge = row.find('span.status-pending, span.status-sukses, span.status-gagal');
                    badge.removeClass('status-pending status-sukses status-gagal')
                         .addClass('status-' + action.toLowerCase())
                         .text(action);
                    row.attr('data-status', action);
                    // Replace aksi button
                    btn.parent().html('<span class="text-xs text-gray-600">Selesai</span>');
                    updateStats();
                } else {
                    showToast(res.message || 'Gagal mengubah status.', 'error');
                    btn.prop('disabled', false).text(action);
                }
            },
            error: function(){
                showToast('Gagal menghubungi server.', 'error');
                btn.prop('disabled', false).text(action);
            }
        });
    });

    /* ---- Update Stats ---- */
    function updateStats(){
        let total = $('.trx-row').length;
        let pending = $('.trx-row[data-status="Pending"]').length;
        let sukses  = $('.trx-row[data-status="Sukses"]').length;
        $('#statTotal').text(total);
        $('#statPending').text(pending);
        $('#statSukses').text(sukses);
    }

    /* ---- Toast ---- */
    function showToast(msg, type){
        let toast = $('#toast');
        toast.removeClass('bg-green-500 bg-red-500')
             .addClass(type === 'success' ? 'bg-green-500' : 'bg-red-500')
             .text(msg).addClass('show');
        setTimeout(function(){ toast.removeClass('show'); }, 3000);
    }
});
</script>
</body>
</html>
