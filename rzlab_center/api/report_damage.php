<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$inventoryId = intval($_POST['inventory_id'] ?? 0);
$reporterName = trim($_POST['reporter_name'] ?? '');
$reporterEmail = trim($_POST['reporter_email'] ?? '');
$reportedCondition = $_POST['reported_condition'] ?? 'Maintenance';
$damageDescription = trim($_POST['damage_description'] ?? '');

if ($inventoryId <= 0 || empty($reporterName) || empty($damageDescription)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib harus diisi.']);
    exit;
}

if (!in_array($reportedCondition, ['Maintenance', 'Damaged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Kondisi tidak valid.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE inventory SET `condition` = ? WHERE id = ?");
    $stmt->execute([$reportedCondition, $inventoryId]);

    $stmt = $pdo->prepare("INSERT INTO damage_reports (inventory_id, reporter_name, reporter_email, damage_description, reported_condition) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$inventoryId, $reporterName, $reporterEmail, $damageDescription, $reportedCondition]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Laporan kerusakan berhasil dikirim.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim laporan. Silakan coba lagi.']);
}
