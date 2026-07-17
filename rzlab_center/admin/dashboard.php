<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'asisten'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/koneksi.php';

$totalItems = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$totalGood = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Good'")->fetchColumn();
$totalMaintenance = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Maintenance'")->fetchColumn();
$totalDamaged = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Damaged'")->fetchColumn();
$totalUnits = $totalGood + $totalMaintenance + $totalDamaged;

$labStats = $pdo->query("SELECT lab_room,
    COUNT(*) as cnt,
    SUM(quantity) as qty,
    SUM(CASE WHEN `condition`='Good' THEN quantity ELSE 0 END) as good_qty,
    SUM(CASE WHEN `condition`='Maintenance' THEN quantity ELSE 0 END) as maint_qty,
    SUM(CASE WHEN `condition`='Damaged' THEN quantity ELSE 0 END) as dmg_qty
    FROM inventory GROUP BY lab_room ORDER BY lab_room")->fetchAll();

$recentReports = $pdo->query("SELECT dr.report_date, dr.damage_description, dr.reported_condition, i.item_name, i.item_code
    FROM damage_reports dr JOIN inventory i ON dr.inventory_id = i.id
    ORDER BY dr.report_date DESC LIMIT 8")->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/../templates/header.php';
?>

<div class="dashboard-grid">
    <div class="stat-card blue">
        <div class="stat-card-header">
            <span class="stat-card-title">Total Unit</span>
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $totalUnits ?></div>
        <div class="stat-card-sub"><?= $totalItems ?> jenis item terdaftar</div>
    </div>

    <div class="stat-card mint">
        <div class="stat-card-header">
            <span class="stat-card-title">Good</span>
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $totalGood ?></div>
        <div class="stat-card-sub">Unit dalam kondisi baik</div>
    </div>

    <div class="stat-card amber">
        <div class="stat-card-header">
            <span class="stat-card-title">Maintenance</span>
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $totalMaintenance ?></div>
        <div class="stat-card-sub">Unit dalam perawatan</div>
    </div>

    <div class="stat-card red">
        <div class="stat-card-header">
            <span class="stat-card-title">Damaged</span>
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $totalDamaged ?></div>
        <div class="stat-card-sub">Unit rusak / tidak berfungsi</div>
    </div>
</div>

<!-- Lab Breakdown -->
<div style="margin-bottom:32px">
    <h3 style="font-size:16px;margin-bottom:16px">Statistik per Laboratorium</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px">
        <?php foreach ($labStats as $lab): ?>
        <div class="content-panel">
            <div class="panel-header" style="padding:16px 20px">
                <h3 class="panel-title" style="font-size:15px"><?= htmlspecialchars($lab['lab_room']) ?></h3>
                <span style="font-size:12px;color:var(--text-muted)"><?= $lab['qty'] ?> unit</span>
            </div>
            <div class="panel-body" style="padding:16px 20px">
                <div style="display:flex;gap:20px;margin-bottom:12px">
                    <div>
                        <span style="font-size:22px;font-weight:800;color:var(--mint-400)"><?= $lab['good_qty'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Good</span>
                    </div>
                    <div>
                        <span style="font-size:22px;font-weight:800;color:var(--amber-400)"><?= $lab['maint_qty'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Maintenance</span>
                    </div>
                    <div>
                        <span style="font-size:22px;font-weight:800;color:var(--red-400)"><?= $lab['dmg_qty'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Damaged</span>
                    </div>
                    <div style="margin-left:auto;text-align:right">
                        <span style="font-size:22px;font-weight:800;color:var(--text-primary)"><?= $lab['cnt'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Jenis</span>
                    </div>
                </div>
                <?php if ($lab['qty'] > 0): ?>
                <div style="height:6px;background:var(--slate-700);border-radius:3px;overflow:hidden;display:flex">
                    <div style="width:<?= round(($lab['good_qty']/$lab['qty'])*100) ?>%;background:var(--mint-500)"></div>
                    <div style="width:<?= round(($lab['maint_qty']/$lab['qty'])*100) ?>%;background:var(--amber-500)"></div>
                    <div style="width:<?= round(($lab['dmg_qty']/$lab['qty'])*100) ?>%;background:var(--red-500)"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Reports -->
<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Laporan Kerusakan Terbaru</h3>
        <button class="btn btn-danger btn-sm" onclick="document.getElementById('btn-laporan-baru').click()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Laporan Baru
        </button>
    </div>
    <div class="panel-body" style="padding:16px 24px">
        <?php if (empty($recentReports)): ?>
            <div class="empty-state" style="padding:32px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <h3>Semua dalam kondisi baik</h3>
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
                            (<?= htmlspecialchars($r['item_code']) ?>) &mdash; <?= htmlspecialchars($r['reported_condition']) ?>
                        </div>
                        <div class="update-text" style="font-size:12px;margin-top:2px;color:var(--text-muted)"><?= htmlspecialchars(mb_strimwidth($r['damage_description'], 0, 80, '...')) ?></div>
                        <div class="update-time"><?= date('d M Y H:i', strtotime($r['report_date'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
