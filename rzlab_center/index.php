<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/config/koneksi.php';

$totalGood = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Good'")->fetchColumn();
$totalMaintenance = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Maintenance'")->fetchColumn();
$totalDamaged = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM inventory WHERE `condition`='Damaged'")->fetchColumn();
$totalAll = $totalGood + $totalMaintenance + $totalDamaged;

$labSummary = $pdo->query("SELECT lab_room, COUNT(*) as types, SUM(quantity) as total_units,
    SUM(CASE WHEN `condition`='Good' THEN quantity ELSE 0 END) as good_units,
    SUM(CASE WHEN `condition`='Maintenance' THEN quantity ELSE 0 END) as maint_units,
    SUM(CASE WHEN `condition`='Damaged' THEN quantity ELSE 0 END) as dmg_units
    FROM inventory GROUP BY lab_room ORDER BY lab_room")->fetchAll();

$labItems = [];
$rooms = ['Lab A', 'Lab B', 'Lab C'];
foreach ($rooms as $room) {
    $labItems[$room] = $pdo->query("SELECT item_code, item_name, brand, category, serial_number, `condition`, quantity
        FROM inventory WHERE lab_room = '" . $room . "' ORDER BY `condition` DESC, item_name")->fetchAll();
}

$inventoryItems = $pdo->query("SELECT id, item_name, item_code FROM inventory ORDER BY item_name")->fetchAll();

$recentReports = $pdo->query("SELECT dr.report_date, dr.reported_condition, dr.damage_description, i.item_name, i.item_code
    FROM damage_reports dr JOIN inventory i ON dr.inventory_id = i.id
    ORDER BY dr.report_date DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RzLab Center - Manajemen Inventaris Lab Komputer</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="min-height:100vh">

<nav style="position:fixed;top:0;left:0;right:0;height:64px;background:rgba(15,22,41,0.9);backdrop-filter:blur(16px);border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;padding:0 32px;z-index:900">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--blue-500),var(--mint-500));border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff">RC</div>
        <span style="font-size:16px;font-weight:700;background:linear-gradient(135deg,#f1f5f9,var(--mint-400));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">RzLab Center</span>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <a href="auth/login.php" class="btn btn-ghost btn-sm">Masuk</a>
    </div>
</nav>

<section class="landing-hero" style="padding-top:120px">
    <h1>Sistem Manajemen Inventaris<br>Laboratorium Komputer</h1>
    <p>Kelola, pantau, dan laporkan kondisi seluruh peralatan laboratorium komputer dalam satu platform terintegrasi.</p>
    <div class="landing-nav">
        <a href="#availability" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Lihat Ketersediaan
        </a>
        <a href="#report" class="btn btn-danger btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Laporkan Kerusakan
        </a>
        <a href="auth/login.php" class="btn btn-success btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard Admin
        </a>
    </div>
</section>

<div style="max-width:1100px;margin:0 auto;padding:0 32px">

    <!-- Stats Cards -->
    <div class="dashboard-grid" style="margin-bottom:48px">
        <div class="stat-card blue">
            <div class="stat-card-header">
                <span class="stat-card-title">Total Unit</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalAll ?></div>
            <div class="stat-card-sub">Seluruh peralatan</div>
        </div>
        <div class="stat-card mint">
            <div class="stat-card-header">
                <span class="stat-card-title">Unit Tersedia</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalGood ?></div>
            <div class="stat-card-sub">Siap digunakan</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-card-header">
                <span class="stat-card-title">Perawatan</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalMaintenance ?></div>
            <div class="stat-card-sub">Dalam perbaikan</div>
        </div>
        <div class="stat-card red">
            <div class="stat-card-header">
                <span class="stat-card-title">Rusak</span>
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </div>
            <div class="stat-card-value"><?= $totalDamaged ?></div>
            <div class="stat-card-sub">Tidak berfungsi</div>
        </div>
    </div>

    <!-- Lab Summary Cards -->
    <div id="availability" style="margin-bottom:48px">
        <h2 style="font-size:24px;margin-bottom:8px">Ketersediaan per Laboratorium</h2>
        <p style="color:var(--text-muted);margin-bottom:20px;font-size:14px">Ringkasan kondisi peralatan di setiap laboratorium.</p>

        <div class="lab-info-grid">
            <?php foreach ($labSummary as $lab): ?>
            <div class="lab-card">
                <h3><?= htmlspecialchars($lab['lab_room']) ?></h3>
                <p><strong style="color:var(--text-primary)"><?= $lab['total_units'] ?></strong> total unit &middot; <strong style="color:var(--text-primary)"><?= $lab['types'] ?></strong> jenis</p>

                <div style="display:flex;gap:16px;margin:12px 0 8px">
                    <div>
                        <span style="font-size:20px;font-weight:800;color:var(--mint-400)"><?= $lab['good_units'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Tersedia</span>
                    </div>
                    <div>
                        <span style="font-size:20px;font-weight:800;color:var(--amber-400)"><?= $lab['maint_units'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Perawatan</span>
                    </div>
                    <div>
                        <span style="font-size:20px;font-weight:800;color:var(--red-400)"><?= $lab['dmg_units'] ?></span>
                        <span style="font-size:11px;color:var(--text-muted);display:block">Rusak</span>
                    </div>
                </div>

                <div style="height:6px;background:var(--slate-700);border-radius:3px;overflow:hidden;display:flex;margin-top:8px">
                    <?php if ($lab['total_units'] > 0): ?>
                    <div style="width:<?= round(($lab['good_units']/$lab['total_units'])*100) ?>%;background:var(--mint-500);transition:width 0.5s"></div>
                    <div style="width:<?= round(($lab['maint_units']/$lab['total_units'])*100) ?>%;background:var(--amber-500);transition:width 0.5s"></div>
                    <div style="width:<?= round(($lab['dmg_units']/$lab['total_units'])*100) ?>%;background:var(--red-500);transition:width 0.5s"></div>
                    <?php endif; ?>
                </div>
                <p style="font-size:11px;margin-top:4px;color:var(--text-muted)"><?= $lab['total_units'] > 0 ? round(($lab['good_units']/$lab['total_units'])*100) : 0 ?>% tersedia</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Per-Lab Inventory Detail -->
    <?php foreach ($rooms as $room): ?>
    <?php $items = $labItems[$room]; ?>
    <div style="margin-bottom:40px">
        <h2 style="font-size:20px;margin-bottom:4px">Daftar Peralatan <?= htmlspecialchars($room) ?></h2>
        <p style="color:var(--text-muted);margin-bottom:16px;font-size:13px"><?= count($items) ?> jenis peralatan terdaftar di laboratorium ini.</p>

        <div class="content-panel">
            <div class="panel-body">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Item</th>
                                <th>Brand</th>
                                <th>Kategori</th>
                                <th>Kondisi</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">Belum ada peralatan di <?= htmlspecialchars($room) ?>.</td></tr>
                            <?php else: foreach ($items as $item): ?>
                            <tr>
                                <td><span class="table-item-code"><?= htmlspecialchars($item['item_code']) ?></span></td>
                                <td><span class="table-item-name"><?= htmlspecialchars($item['item_name']) ?></span></td>
                                <td><?= htmlspecialchars($item['brand']) ?></td>
                                <td><span class="badge-category"><?= htmlspecialchars($item['category']) ?></span></td>
                                <td>
                                    <?php
                                    $cond = $item['condition'];
                                    $cls = strtolower($cond);
                                    ?>
                                    <span class="badge-status badge-<?= $cls ?>"><?= htmlspecialchars($cond) ?></span>
                                </td>
                                <td><strong style="color:var(--text-primary)"><?= $item['quantity'] ?></strong></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Damage Report -->
    <div id="report" class="report-form-card" style="max-width:100%">
        <h2>Laporkan Kerusakan</h2>
        <p class="subtitle">Temukan masalah pada peralatan lab? Laporkan di sini agar segera ditangani.</p>

        <div id="report-success-msg" class="report-success">
            Laporan kerusakan berhasil dikirim! Terima kasih atas kontribusi Anda.
        </div>

        <form id="form-damage-report">
            <div class="form-group">
                <label class="form-label">Pilih Item</label>
                <select name="inventory_id" class="form-control" required>
                    <option value="">-- Pilih Item --</option>
                    <?php foreach ($inventoryItems as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (<?= htmlspecialchars($item['item_code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Pelapor</label>
                    <input type="text" name="reporter_name" class="form-control" placeholder="Nama Anda" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="reporter_email" class="form-control" placeholder="email@kampus.ac.id">
                </div>
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
                <textarea name="damage_description" class="form-control" placeholder="Jelaskan kerusakan yang Anda temukan..." required></textarea>
            </div>
            <button type="submit" class="btn btn-danger" style="margin-top:8px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Kirim Laporan
            </button>
        </form>
    </div>

    <!-- Recent Reports -->
    <?php if (!empty($recentReports)): ?>
    <div class="content-panel" style="margin:40px auto">
        <div class="panel-header">
            <h3 class="panel-title">Laporan Kerusakan Terbaru</h3>
        </div>
        <div class="panel-body" style="padding:16px 24px">
            <div class="recent-updates">
                <?php foreach ($recentReports as $r): ?>
                <div class="update-item">
                    <div class="update-dot <?= strtolower($r['reported_condition']) ?>"></div>
                    <div>
                        <div class="update-text">
                            <strong><?= htmlspecialchars($r['item_name']) ?></strong> (<?= htmlspecialchars($r['item_code']) ?>) &mdash; <?= htmlspecialchars($r['reported_condition']) ?>
                        </div>
                        <div class="update-text" style="font-size:12px;margin-top:2px"><?= htmlspecialchars(mb_strimwidth($r['damage_description'], 0, 80, '...')) ?></div>
                        <div class="update-time"><?= date('d M Y H:i', strtotime($r['report_date'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer style="text-align:center;padding:40px 0;border-top:1px solid var(--border-color);margin-top:40px;color:var(--text-muted);font-size:13px">
        <p>&copy; <?= date('Y') ?> RzLab Center. Sistem Manajemen Inventaris Lab Komputer.</p>
    </footer>

</div>

<script src="assets/js/main.js"></script>
</body>
</html>
