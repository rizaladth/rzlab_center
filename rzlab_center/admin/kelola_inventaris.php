<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] !== 'list' && $_GET['action'] !== 'detail')) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'asisten'])) {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $_POST = $_POST;
    }

    $action = $input['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $item_code = trim($_POST['item_code'] ?? '');
            $item_name = trim($_POST['item_name'] ?? '');
            $brand = trim($_POST['brand'] ?? '');
            $category = $_POST['category'] ?? '';
            $serial_number = trim($_POST['serial_number'] ?? '');
            $condition = $_POST['condition'] ?? 'Good';
            $lab_room = $_POST['lab_room'] ?? 'Lab A';
            $quantity = intval($_POST['quantity'] ?? 1);

            if (empty($item_code) || empty($item_name) || empty($brand) || empty($serial_number)) {
                echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi.']);
                exit;
            }

            $validCategories = ['PC', 'Monitor', 'Laptop', 'Networking', 'Accessories'];
            $validConditions = ['Good', 'Maintenance', 'Damaged'];
            $validRooms = ['Lab A', 'Lab B', 'Lab C'];

            if (!in_array($category, $validCategories)) {
                echo json_encode(['status' => 'error', 'message' => 'Kategori tidak valid.']);
                exit;
            }
            if (!in_array($condition, $validConditions)) {
                echo json_encode(['status' => 'error', 'message' => 'Kondisi tidak valid.']);
                exit;
            }
            if (!in_array($lab_room, $validRooms)) {
                echo json_encode(['status' => 'error', 'message' => 'Lab room tidak valid.']);
                exit;
            }
            if ($quantity < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Jumlah minimal 1.']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO inventory (item_code, item_name, brand, category, serial_number, `condition`, lab_room, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$item_code, $item_name, $brand, $category, $serial_number, $condition, $lab_room, $quantity]);
                echo json_encode(['status' => 'success', 'message' => 'Item berhasil ditambahkan.']);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode item atau nomor seri sudah ada.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan item.']);
                }
            }
            break;

        case 'update':
            $id = intval($_POST['id'] ?? $input['id'] ?? 0);
            $item_code = trim($_POST['item_code'] ?? $input['item_code'] ?? '');
            $item_name = trim($_POST['item_name'] ?? $input['item_name'] ?? '');
            $brand = trim($_POST['brand'] ?? $input['brand'] ?? '');
            $category = $_POST['category'] ?? $input['category'] ?? '';
            $serial_number = trim($_POST['serial_number'] ?? $input['serial_number'] ?? '');
            $condition = $_POST['condition'] ?? $input['condition'] ?? 'Good';
            $lab_room = $_POST['lab_room'] ?? $input['lab_room'] ?? 'Lab A';
            $quantity = intval($_POST['quantity'] ?? $input['quantity'] ?? 1);

            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE inventory SET item_code=?, item_name=?, brand=?, category=?, serial_number=?, `condition`=?, lab_room=?, quantity=? WHERE id=?");
                $stmt->execute([$item_code, $item_name, $brand, $category, $serial_number, $condition, $lab_room, $quantity, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Item berhasil diperbarui.']);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode item atau nomor seri sudah digunakan.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui item.']);
                }
            }
            break;

        case 'delete':
            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Item berhasil dihapus.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus item.']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
    exit;
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'asisten'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
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
    $category = $_GET['category'] ?? '';
    $condition = $_GET['condition'] ?? '';
    $room = $_GET['room'] ?? '';

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(item_code LIKE ? OR item_name LIKE ? OR brand LIKE ? OR serial_number LIKE ?)";
        $like = "%{$search}%";
        $params = array_merge($params, [$like, $like, $like, $like]);
    }
    if ($category !== '') {
        $where[] = "category = ?";
        $params[] = $category;
    }
    if ($condition !== '') {
        $where[] = "`condition` = ?";
        $params[] = $condition;
    }
    if ($room !== '') {
        $where[] = "lab_room = ?";
        $params[] = $room;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inventory {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM inventory {$whereClause} ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $p) {
        $stmt->bindValue($i + 1, $p);
    }
    $stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $items,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage
    ]);
    exit;
}

if ($action === 'detail') {
    header('Content-Type: application/json');
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        echo json_encode(['status' => 'success', 'data' => $item]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
    }
    exit;
}

$pageTitle = 'Kelola Inventaris';
include __DIR__ . '/../templates/header.php';
?>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Data Inventaris</h3>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="openModal('modal-add')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Item
            </button>
        </div>
    </div>

    <div class="filter-bar">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="filter-search" placeholder="Cari item, kode, brand...">
        </div>
        <select id="filter-category" class="filter-select">
            <option value="">Semua Kategori</option>
            <option value="PC">PC</option>
            <option value="Monitor">Monitor</option>
            <option value="Laptop">Laptop</option>
            <option value="Networking">Networking</option>
            <option value="Accessories">Accessories</option>
        </select>
        <select id="filter-condition" class="filter-select">
            <option value="">Semua Kondisi</option>
            <option value="Good">Good</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Damaged">Damaged</option>
        </select>
        <select id="filter-room" class="filter-select">
            <option value="">Semua Lab</option>
            <option value="Lab A">Lab A</option>
            <option value="Lab B">Lab B</option>
            <option value="Lab C">Lab C</option>
        </select>
    </div>

    <div class="panel-body">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Item</th>
                        <th>Brand</th>
                        <th>Kategori</th>
                        <th>Serial Number</th>
                        <th>Kondisi</th>
                        <th>Lab</th>
                        <th>Qty</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="inventory-tbody">
                    <tr><td colspan="9" style="text-align:center;padding:40px"><div class="spinner" style="margin:0 auto"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        <span class="pagination-info" id="pagination-info">Memuat data...</span>
        <div class="pagination-btns" id="pagination-btns"></div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Item Inventaris</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="form-add-item" class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Item</label>
                    <input type="text" name="item_code" class="form-control" placeholder="PC-LA-003" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="SN-PC-2024-005" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Item</label>
                <input type="text" name="item_name" class="form-control" placeholder="Desktop Workstation" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Dell" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="PC">PC</option>
                        <option value="Monitor">Monitor</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Networking">Networking</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kondisi</label>
                    <select name="condition" class="form-control" required>
                        <option value="Good">Good</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lab Room</label>
                    <select name="lab_room" class="form-control" required>
                        <option value="Lab A">Lab A</option>
                        <option value="Lab B">Lab B</option>
                        <option value="Lab C">Lab C</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah</label>
                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
            </div>
            <div class="modal-footer" style="padding:16px 0 0;border-top:none">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add')">Batal</button>
                <button type="submit" class="btn btn-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Item Inventaris</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="form-edit-item" class="modal-body">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Item</label>
                    <input type="text" name="item_code" id="edit-item_code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" id="edit-serial_number" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Item</label>
                <input type="text" name="item_name" id="edit-item_name" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" id="edit-brand" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" id="edit-category" class="form-control" required>
                        <option value="PC">PC</option>
                        <option value="Monitor">Monitor</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Networking">Networking</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kondisi</label>
                    <select name="condition" id="edit-condition" class="form-control" required>
                        <option value="Good">Good</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lab Room</label>
                    <select name="lab_room" id="edit-lab_room" class="form-control" required>
                        <option value="Lab A">Lab A</option>
                        <option value="Lab B">Lab B</option>
                        <option value="Lab C">Lab C</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah</label>
                <input type="number" name="quantity" id="edit-quantity" class="form-control" min="1" required>
            </div>
            <div class="modal-footer" style="padding:16px 0 0;border-top:none">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
