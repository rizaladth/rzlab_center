<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/koneksi.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = $_SESSION['username'] ?? 'Guest';
$currentRole = $_SESSION['role'] ?? 'user';
$initials = strtoupper(substr($currentUser, 0, 2));

$notifCount = 0;
$notifications = [];
$userProfile = null;
if (isset($pdo)) {
    try {
        $notifCount = $pdo->query("SELECT COUNT(*) FROM damage_reports WHERE report_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $notifications = $pdo->query("SELECT dr.report_date, dr.reported_condition, dr.damage_description, i.item_name, i.item_code
            FROM damage_reports dr JOIN inventory i ON dr.inventory_id = i.id
            ORDER BY dr.report_date DESC LIMIT 10")->fetchAll();

        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userProfile = $stmt->fetch();
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'RzLab Center') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .notif-dropdown{position:absolute;top:calc(100% + 8px);right:0;width:380px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--border-radius);box-shadow:var(--shadow-xl);z-index:2100;opacity:0;visibility:hidden;transform:translateY(-8px);transition:all var(--transition-fast);max-height:420px;overflow:hidden;display:flex;flex-direction:column}
        .notif-dropdown.active{opacity:1;visibility:visible;transform:translateY(0)}
        .notif-header{padding:14px 16px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center}
        .notif-header h4{font-size:14px;font-weight:700}
        .notif-list{overflow-y:auto;flex:1}
        .notif-item{display:flex;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-color);transition:background var(--transition-fast);cursor:default}
        .notif-item:hover{background:var(--bg-card)}
        .notif-item:last-child{border-bottom:none}
        .notif-dot{width:8px;height:8px;border-radius:50%;margin-top:5px;flex-shrink:0}
        .notif-dot.good{background:var(--mint-400)}
        .notif-dot.maintenance{background:var(--amber-400)}
        .notif-dot.damaged{background:var(--red-400)}
        .notif-body{flex:1;min-width:0}
        .notif-body p{font-size:12px;color:var(--text-secondary);line-height:1.4}
        .notif-body p strong{color:var(--text-primary)}
        .notif-body small{font-size:11px;color:var(--text-muted);margin-top:2px;display:block}
        .notif-empty{padding:32px;text-align:center;color:var(--text-muted);font-size:13px}

        .profile-dropdown{position:absolute;bottom:calc(100% + 8px);left:12px;right:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--border-radius);box-shadow:var(--shadow-xl);z-index:1100;opacity:0;visibility:hidden;transform:translateY(8px);transition:all var(--transition-fast);overflow:hidden}
        .profile-dropdown.active{opacity:1;visibility:visible;transform:translateY(0)}
        .profile-card{padding:20px}
        .profile-card .profile-avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--blue-500),var(--mint-500));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;color:#fff;margin:0 auto 12px}
        .profile-card .profile-name{text-align:center;font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:2px}
        .profile-card .profile-email{text-align:center;font-size:12px;color:var(--text-muted);margin-bottom:12px}
        .profile-detail-row{display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--border-color);font-size:13px}
        .profile-detail-row .label{color:var(--text-muted)}
        .profile-detail-row .value{color:var(--text-primary);font-weight:600;text-transform:capitalize}
        .profile-actions{padding:12px 20px;border-top:1px solid var(--border-color);display:flex;gap:8px}
        .profile-actions .btn{flex:1;justify-content:center;font-size:12px}
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">RC</div>
            <div class="sidebar-brand">
                <h2>RzLab Center</h2>
                <small>Inventory System</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-label">Menu Utama</div>

            <a href="../admin/dashboard.php" class="sidebar-nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>

            <a href="../admin/kelola_inventaris.php" class="sidebar-nav-item <?= $currentPage === 'kelola_inventaris.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Kelola Inventaris
            </a>

            <a href="../admin/kelola_laporan.php" class="sidebar-nav-item <?= $currentPage === 'kelola_laporan.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Kelola Laporan
            </a>

            <div class="sidebar-nav-label">Menu</div>

            <a href="../admin/beranda.php" class="sidebar-nav-item <?= $currentPage === 'beranda.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Beranda
            </a>
        </nav>

        <div class="sidebar-footer" style="position:relative">
            <div class="sidebar-user" id="profile-toggle" style="cursor:pointer">
                <div class="sidebar-avatar"><?= $initials ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars($currentUser) ?></div>
                    <div class="sidebar-user-role"><?= htmlspecialchars($currentRole) ?></div>
                </div>
            </div>

            <div class="profile-dropdown" id="profile-dropdown">
                <div class="profile-card">
                    <div class="profile-avatar"><?= $initials ?></div>
                    <div class="profile-name"><?= htmlspecialchars($userProfile['username'] ?? $currentUser) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($userProfile['email'] ?? '-') ?></div>
                    <div class="profile-detail-row">
                        <span class="label">Role</span>
                        <span class="value"><?= htmlspecialchars($userProfile['role'] ?? $currentRole) ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="label">User ID</span>
                        <span class="value">#<?= htmlspecialchars($userProfile['id'] ?? '-') ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="label">Bergabung</span>
                        <span class="value"><?= $userProfile['created_at'] ? date('d M Y', strtotime($userProfile['created_at'])) : '-' ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="label">Status</span>
                        <span class="value" style="color:var(--mint-400)">Aktif</span>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="../admin/beranda.php" class="btn btn-ghost btn-sm">Beranda</a>
                    <a href="../auth/logout.php" class="btn btn-danger btn-sm">Keluar</a>
                </div>
            </div>
        </div>
    </aside>

    <header class="top-header">
        <div class="header-left">
            <button class="sidebar-toggle" title="Toggle Sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        </div>
        <div class="header-right">
            <div class="header-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="header-search" placeholder="Cari item, kode, brand...">
            </div>

            <button class="btn btn-success btn-sm" id="btn-laporan-baru" style="margin-left:4px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Laporan Baru
            </button>

            <div style="position:relative" id="notif-wrapper">
                <button class="header-btn" title="Notifikasi" id="notif-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if ($notifCount > 0): ?>
                    <span class="badge" id="notif-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="notif-dropdown" id="notif-dropdown">
                    <div class="notif-header">
                        <h4>Notifikasi</h4>
                        <span style="font-size:11px;color:var(--text-muted)"><?= $notifCount ?> laporan 7 hari terakhir</span>
                    </div>
                    <div class="notif-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notif-empty">Belum ada notifikasi</div>
                        <?php else: foreach ($notifications as $n): ?>
                        <div class="notif-item">
                            <div class="notif-dot <?= strtolower($n['reported_condition']) ?>"></div>
                            <div class="notif-body">
                                <p><strong><?= htmlspecialchars($n['item_name']) ?></strong> (<?= htmlspecialchars($n['item_code']) ?>) dilaporkan <strong><?= htmlspecialchars($n['reported_condition']) ?></strong></p>
                                <small><?= date('d M Y H:i', strtotime($n['report_date'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Laporan Baru Modal (shared across all admin pages) -->
    <div class="modal-overlay" id="modal-laporan-baru">
        <div class="modal">
            <div class="modal-header">
                <h3>Laporan Kerusakan Baru</h3>
                <button class="modal-close" onclick="closeModal('modal-laporan-baru')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="form-admin-damage-report" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Pilih Item</label>
                    <select name="inventory_id" id="laporan-item-select" class="form-control" required>
                        <option value="">-- Memuat data... --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Pelapor</label>
                    <input type="text" name="reporter_name" class="form-control" value="<?= htmlspecialchars($currentUser) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kondisi yang Diamati</label>
                    <select name="reported_condition" class="form-control" required>
                        <option value="Maintenance">Maintenance (Perlu Perbaikan)</option>
                        <option value="Damaged">Damaged (Rusak Parah)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Kerusakan</label>
                    <textarea name="damage_description" class="form-control" placeholder="Jelaskan kerusakan yang ditemukan..." required></textarea>
                </div>
                <div class="modal-footer" style="padding:16px 0 0;border-top:none">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('modal-laporan-baru')">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <main class="main-content">
