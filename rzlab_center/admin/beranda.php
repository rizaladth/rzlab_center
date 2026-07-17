<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'asisten'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/koneksi.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$totalItems = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$totalUnits = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory")->fetchColumn();
$totalGood = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Good'")->fetchColumn();
$totalReports = $pdo->query("SELECT COUNT(*) FROM damage_reports")->fetchColumn();

$recentReports = $pdo->query("SELECT dr.report_date, dr.reported_condition, i.item_name, i.item_code
    FROM damage_reports dr JOIN inventory i ON dr.inventory_id = i.id
    ORDER BY dr.report_date DESC LIMIT 5")->fetchAll();

$pageTitle = 'Beranda';
include __DIR__ . '/../templates/header.php';
?>

<!-- Welcome Banner -->
<div style="background:linear-gradient(135deg, rgba(37,99,235,0.15), rgba(16,185,129,0.1));border:1px solid var(--border-color);border-radius:var(--border-radius);padding:32px;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap">
    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--blue-500),var(--mint-500));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:24px;color:#fff;flex-shrink:0">
        <?= strtoupper(substr($user['username'] ?? 'U', 0, 2)) ?>
    </div>
    <div style="flex:1;min-width:200px">
        <h2 style="font-size:22px;margin-bottom:4px">Selamat datang, <?= htmlspecialchars($user['username'] ?? '') ?>!</h2>
        <p style="color:var(--text-secondary);font-size:14px">
            <?php
            $hour = date('H');
            if ($hour < 12) $greeting = 'Selamat pagi';
            elseif ($hour < 18) $greeting = 'Selamat siang';
            else $greeting = 'Selamat malam';
            ?>
            <?= $greeting ?>. Berikut ringkasan aktivitas laboratorium hari ini.
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="kelola_inventaris.php" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Kelola Inventaris
        </a>
        <button class="btn btn-danger btn-sm" onclick="document.getElementById('btn-laporan-baru')?.click()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Laporan Baru
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div class="dashboard-grid" style="margin-bottom:32px">
    <a href="dashboard.php" style="text-decoration:none">
        <div class="stat-card blue" style="cursor:pointer">
            <div class="stat-card-header">
                <span class="stat-card-title">Total Unit</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalUnits ?></div>
            <div class="stat-card-sub"><?= $totalItems ?> jenis item</div>
        </div>
    </a>
    <div class="stat-card mint">
        <div class="stat-card-header">
            <span class="stat-card-title">Tersedia</span>
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $totalGood ?></div>
        <div class="stat-card-sub">Siap digunakan</div>
    </div>
    <a href="dashboard.php" style="text-decoration:none">
        <div class="stat-card red" style="cursor:pointer">
            <div class="stat-card-header">
                <span class="stat-card-title">Laporan</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalReports ?></div>
            <div class="stat-card-sub">Total laporan kerusakan</div>
        </div>
    </a>
</div>

<!-- Profile & Recent Reports -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px">
    <!-- Profile Card -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Profil Akun</h3>
        </div>
        <div class="panel-body" style="padding:24px">
            <div style="text-align:center;margin-bottom:20px">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--blue-500),var(--mint-500));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:24px;color:#fff;margin:0 auto 12px">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 2)) ?>
                </div>
                <h3 style="font-size:18px;margin-bottom:2px"><?= htmlspecialchars($user['username'] ?? '') ?></h3>
                <p style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
            <div class="profile-detail-row">
                <span class="label">User ID</span>
                <span class="value">#<?= htmlspecialchars($user['id'] ?? '') ?></span>
            </div>
            <div class="profile-detail-row">
                <span class="label">Role</span>
                <span class="value"><?= htmlspecialchars($user['role'] ?? '') ?></span>
            </div>
            <div class="profile-detail-row">
                <span class="label">Bergabung</span>
                <span class="value"><?= $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : '-' ?></span>
            </div>
            <div class="profile-detail-row">
                <span class="label">Status</span>
                <span class="value" style="color:var(--mint-400)">Aktif</span>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Laporan Terbaru</h3>
            <a href="dashboard.php" class="btn btn-ghost btn-sm">Lihat Semua</a>
        </div>
        <div class="panel-body" style="padding:16px 24px">
            <?php if (empty($recentReports)): ?>
                <div class="empty-state" style="padding:24px">
                    <p>Belum ada laporan kerusakan.</p>
                </div>
            <?php else: ?>
                <div class="recent-updates">
                    <?php foreach ($recentReports as $r): ?>
                    <div class="update-item">
                        <div class="update-dot <?= strtolower($r['reported_condition']) ?>"></div>
                        <div>
                            <div class="update-text">
                                <strong><?= htmlspecialchars($r['item_name']) ?></strong>
                                (<?= htmlspecialchars($r['item_code']) ?>)
                            </div>
                            <div class="update-time"><?= date('d M Y H:i', strtotime($r['report_date'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Aksi Cepat</h3>
    </div>
    <div class="panel-body" style="padding:24px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
            <a href="kelola_inventaris.php" class="btn btn-primary" style="justify-content:center;padding:20px;flex-direction:column;gap:8px;height:auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span style="font-size:14px">Kelola Inventaris</span>
            </a>
            <a href="dashboard.php" class="btn btn-ghost" style="justify-content:center;padding:20px;flex-direction:column;gap:8px;height:auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span style="font-size:14px">Dashboard Statistik</span>
            </a>
            <button class="btn btn-danger" onclick="document.getElementById('btn-laporan-baru')?.click()" style="justify-content:center;padding:20px;flex-direction:column;gap:8px;height:auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span style="font-size:14px">Laporkan Kerusakan</span>
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
