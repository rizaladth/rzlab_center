<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'asisten'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    } else {
        header('Location: ../auth/login.php');
    }
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    header('Content-Type: application/json');

    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(100, intval($_GET['per_page'] ?? 10)));
    $search = trim($_GET['search'] ?? '');
    $condition = $_GET['condition'] ?? '';

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(dr.reporter_name LIKE ? OR dr.reporter_email LIKE ? OR dr.damage_description LIKE ? OR i.item_name LIKE ? OR i.item_code LIKE ?)";
        $like = "%{$search}%";
        $params = array_merge($params, [$like, $like, $like, $like, $like]);
    }
    if ($condition !== '') {
        $where[] = "dr.reported_condition = ?";
        $params[] = $condition;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSql = "SELECT COUNT(*) FROM damage_reports dr JOIN inventory i ON dr.inventory_id = i.id {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT dr.*, i.item_name, i.item_code, i.brand, i.lab_room, i.`condition` AS inventory_condition
            FROM damage_reports dr
            JOIN inventory i ON dr.inventory_id = i.id
            {$whereClause}
            ORDER BY dr.report_date DESC
            LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $p) {
        $stmt->bindValue($i + 1, $p);
    }
    $stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $reports,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage
    ]);
    exit;
}

if ($action === 'detail') {
    header('Content-Type: application/json');
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT dr.*, i.item_name, i.item_code, i.brand, i.lab_room, i.category, i.serial_number,
                           i.`condition` AS inventory_condition
                           FROM damage_reports dr
                           JOIN inventory i ON dr.inventory_id = i.id
                           WHERE dr.id = ?");
    $stmt->execute([$id]);
    $report = $stmt->fetch();
    if ($report) {
        echo json_encode(['status' => 'success', 'data' => $report]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $postAction = $input['action'] ?? $_POST['action'] ?? '';

    switch ($postAction) {
        case 'delete':
            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("DELETE FROM damage_reports WHERE id = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Laporan berhasil dihapus.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus laporan.']);
            }
            exit;

        case 'update_condition':
            $id = intval($input['id'] ?? 0);
            $newCondition = $input['condition'] ?? '';
            if ($id <= 0 || !in_array($newCondition, ['Good', 'Maintenance', 'Damaged'])) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
                exit;
            }
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT inventory_id FROM damage_reports WHERE id = ?");
                $stmt->execute([$id]);
                $report = $stmt->fetch();

                if (!$report) {
                    $pdo->rollBack();
                    echo json_encode(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE inventory SET `condition` = ? WHERE id = ?");
                $stmt->execute([$newCondition, $report['inventory_id']]);

                $stmt = $pdo->prepare("UPDATE damage_reports SET reported_condition = ? WHERE id = ?");
                $stmt->execute([$newCondition === 'Good' ? 'Maintenance' : $newCondition, $id]);

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Kondisi inventaris berhasil diperbarui.']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui kondisi.']);
            }
            exit;

        case 'resolve':
            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
                exit;
            }
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT inventory_id FROM damage_reports WHERE id = ?");
                $stmt->execute([$id]);
                $report = $stmt->fetch();

                if (!$report) {
                    $pdo->rollBack();
                    echo json_encode(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE inventory SET `condition` = 'Good' WHERE id = ?");
                $stmt->execute([$report['inventory_id']]);

                $stmt = $pdo->prepare("DELETE FROM damage_reports WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Laporan ditandai selesai dan item dikembalikan ke Good.']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyelesaikan laporan.']);
            }
            exit;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
            exit;
    }
}

$pageTitle = 'Kelola Laporan';
include __DIR__ . '/../templates/header.php';
?>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Laporan Kerusakan</h3>
    </div>

    <div class="filter-bar">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="filter-search" placeholder="Cari pelapor, item, deskripsi...">
        </div>
        <select id="filter-condition" class="filter-select">
            <option value="">Semua Kondisi</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Damaged">Damaged</option>
        </select>
    </div>

    <div class="panel-body">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Pelapor</th>
                        <th>Kondisi Laporan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="report-tbody">
                    <tr><td colspan="6" style="text-align:center;padding:40px"><div class="spinner" style="margin:0 auto"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        <span class="pagination-info" id="pagination-info">Memuat data...</span>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="modal-detail">
    <div class="modal">
        <div class="modal-header">
            <h3>Detail Laporan</h3>
            <button class="modal-close" onclick="closeModal('modal-detail')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" id="detail-content">
            <div style="text-align:center;padding:20px"><div class="spinner" style="margin:0 auto"></div></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-detail')">Tutup</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>

<script>
(function() {
    const ReportManager = {
        currentPage: 1,
        perPage: 10,
        searchQuery: '',
        filterCondition: '',

        async load() {
            const params = new URLSearchParams({
                action: 'list',
                page: this.currentPage,
                per_page: this.perPage,
                search: this.searchQuery,
                condition: this.filterCondition
            });

            const result = await ajaxRequest(`${BASE}admin/kelola_laporan.php?${params}`);

            if (result.status === 'success') {
                this.renderTable(result.data);
                this.renderPagination(result.total, result.page, result.per_page);
            } else {
                showToast(result.message || 'Gagal memuat data', 'error');
            }
        },

        renderTable(reports) {
            const tbody = document.getElementById('report-tbody');
            if (!tbody) return;

            if (!reports || reports.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <h3>Tidak ada laporan</h3>
                            <p>Belum ada laporan kerusakan yang ditemukan.</p>
                        </div>
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = reports.map(r => {
                const condClass = r.reported_condition.toLowerCase();
                const date = new Date(r.report_date);
                const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                return `
                <tr data-id="${r.id}">
                    <td><span class="mono" style="font-size:12px">#${r.id}</span></td>
                    <td>
                        <span class="table-item-name">${escapeHtml(r.item_name)}</span><br>
                        <span class="mono" style="font-size:11px;color:var(--text-muted)">${escapeHtml(r.item_code)}</span>
                    </td>
                    <td>
                        <span style="font-weight:600">${escapeHtml(r.reporter_name)}</span><br>
                        <span style="font-size:11px;color:var(--text-muted)">${escapeHtml(r.reporter_email || '-')}</span>
                    </td>
                    <td><span class="badge-status badge-${condClass}">${escapeHtml(r.reported_condition)}</span></td>
                    <td style="font-size:12px">${dateStr}<br><span style="color:var(--text-muted)">${timeStr}</span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="ReportManager.detail(${r.id})" title="Detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="ReportManager.resolve(${r.id})" title="Selesaikan" style="color:var(--mint-400)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </button>
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="ReportManager.remove(${r.id})" title="Hapus" style="color:var(--red-400)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        },

        renderPagination(total, page, perPage) {
            const totalPages = Math.ceil(total / perPage);
            const infoEl = document.getElementById('pagination-info');
            const btnsEl = document.getElementById('pagination-btns');
            if (!infoEl || !btnsEl) return;

            const start = (page - 1) * perPage + 1;
            const end = Math.min(page * perPage, total);
            infoEl.textContent = total > 0
                ? `Menampilkan ${start}-${end} dari ${total} laporan`
                : 'Tidak ada data';

            let btns = '';
            if (page > 1) {
                btns += `<button class="btn btn-ghost btn-sm" onclick="ReportManager.goToPage(${page - 1})">Sebelumnya</button>`;
            }
            for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
                btns += `<button class="btn ${i === page ? 'btn-primary' : 'btn-ghost'} btn-sm" onclick="ReportManager.goToPage(${i})">${i}</button>`;
            }
            if (page < totalPages) {
                btns += `<button class="btn btn-ghost btn-sm" onclick="ReportManager.goToPage(${page + 1})">Selanjutnya</button>`;
            }
            btnsEl.innerHTML = btns;
        },

        goToPage(page) {
            this.currentPage = page;
            this.load();
        },

        async detail(id) {
            const content = document.getElementById('detail-content');
            content.innerHTML = '<div style="text-align:center;padding:20px"><div class="spinner" style="margin:0 auto"></div></div>';
            openModal('modal-detail');

            const result = await ajaxRequest(`${BASE}admin/kelola_laporan.php?action=detail&id=${id}`);
            if (result.status === 'success') {
                const r = result.data;
                const condClass = r.reported_condition.toLowerCase();
                const invCondClass = (r.inventory_condition || '').toLowerCase();
                const date = new Date(r.report_date);
                const dateStr = date.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
                const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                content.innerHTML = `
                    <div style="display:grid;gap:16px">
                        <div class="profile-detail-row">
                            <span class="label">ID Laporan</span>
                            <span class="value">#${r.id}</span>
                        </div>
                        <div class="profile-detail-row">
                            <span class="label">Tanggal</span>
                            <span class="value">${dateStr} ${timeStr}</span>
                        </div>

                        <div style="border-top:1px solid var(--border-color);padding-top:16px">
                            <h4 style="font-size:13px;color:var(--text-muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px">Informasi Item</h4>
                            <div class="profile-detail-row">
                                <span class="label">Kode</span>
                                <span class="value">${escapeHtml(r.item_code)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Nama</span>
                                <span class="value">${escapeHtml(r.item_name)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Brand</span>
                                <span class="value">${escapeHtml(r.brand)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Kategori</span>
                                <span class="value">${escapeHtml(r.category)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Serial Number</span>
                                <span class="value">${escapeHtml(r.serial_number)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Lab</span>
                                <span class="value">${escapeHtml(r.lab_room)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Kondisi Saat Ini</span>
                                <span class="value"><span class="badge-status badge-${invCondClass}">${escapeHtml(r.inventory_condition)}</span></span>
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--border-color);padding-top:16px">
                            <h4 style="font-size:13px;color:var(--text-muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px">Laporan</h4>
                            <div class="profile-detail-row">
                                <span class="label">Pelapor</span>
                                <span class="value">${escapeHtml(r.reporter_name)}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Email</span>
                                <span class="value">${escapeHtml(r.reporter_email || '-')}</span>
                            </div>
                            <div class="profile-detail-row">
                                <span class="label">Kondisi Dilaporkan</span>
                                <span class="value"><span class="badge-status badge-${condClass}">${escapeHtml(r.reported_condition)}</span></span>
                            </div>
                            <div style="margin-top:8px">
                                <span class="label" style="display:block;margin-bottom:4px;font-size:13px;color:var(--text-muted)">Deskripsi Kerusakan</span>
                                <div style="background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:var(--border-radius);padding:12px;font-size:13px;color:var(--text-primary);line-height:1.6">${escapeHtml(r.damage_description).replace(/\n/g, '<br>')}</div>
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--border-color);padding-top:16px;display:flex;gap:8px;flex-wrap:wrap">
                            <button class="btn btn-success btn-sm" onclick="ReportManager.resolve(${r.id});closeModal('modal-detail')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Tandai Selesai
                            </button>
                            <select id="detail-change-cond" class="form-control" style="width:auto;font-size:12px;padding:4px 8px">
                                <option value="">Ubah Kondisi Item...</option>
                                <option value="Good">Good</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                            <button class="btn btn-primary btn-sm" onclick="ReportManager.changeCondition(${r.id})">
                                Simpan Kondisi
                            </button>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="empty-state"><p>Gagal memuat detail laporan.</p></div>';
            }
        },

        async changeCondition(id) {
            const select = document.getElementById('detail-change-cond');
            const newCond = select ? select.value : '';
            if (!newCond) {
                showToast('Pilih kondisi terlebih dahulu', 'info');
                return;
            }
            const result = await ajaxRequest(`${BASE}admin/kelola_laporan.php`, {
                method: 'POST',
                body: JSON.stringify({ action: 'update_condition', id: id, condition: newCond }),
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (result.status === 'success') {
                showToast('Kondisi inventaris berhasil diperbarui', 'success');
                closeModal('modal-detail');
                this.load();
            } else {
                showToast(result.message || 'Gagal memperbarui kondisi', 'error');
            }
        },

        async resolve(id) {
            if (!confirm('Tandai laporan ini selesai? Item akan dikembalikan ke kondisi Good dan laporan akan dihapus.')) return;
            const result = await ajaxRequest(`${BASE}admin/kelola_laporan.php`, {
                method: 'POST',
                body: JSON.stringify({ action: 'resolve', id: id }),
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (result.status === 'success') {
                showToast('Laporan berhasil diselesaikan', 'success');
                this.load();
            } else {
                showToast(result.message || 'Gagal menyelesaikan laporan', 'error');
            }
        },

        async remove(id) {
            if (!confirm('Yakin ingin menghapus laporan ini?')) return;
            const result = await ajaxRequest(`${BASE}admin/kelola_laporan.php`, {
                method: 'POST',
                body: JSON.stringify({ action: 'delete', id: id }),
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (result.status === 'success') {
                showToast('Laporan berhasil dihapus', 'success');
                this.load();
            } else {
                showToast(result.message || 'Gagal menghapus laporan', 'error');
            }
        }
    };

    window.ReportManager = ReportManager;

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('filter-search');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    ReportManager.searchQuery = this.value;
                    ReportManager.currentPage = 1;
                    ReportManager.load();
                }, 300);
            });
        }

        const condSelect = document.getElementById('filter-condition');
        if (condSelect) {
            condSelect.addEventListener('change', function() {
                ReportManager.filterCondition = this.value;
                ReportManager.currentPage = 1;
                ReportManager.load();
            });
        }

        if (document.getElementById('report-tbody')) {
            ReportManager.load();
        }
    });
})();
</script>
