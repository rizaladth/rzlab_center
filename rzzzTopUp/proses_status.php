<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid.']);
    exit;
}

$allowed = ['Pending', 'Sukses', 'Gagal'];
if (!in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE transaksi SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status.']);
}
