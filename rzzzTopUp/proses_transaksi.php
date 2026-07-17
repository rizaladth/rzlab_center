<?php
require 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$user_id_game = isset($_POST['user_id_game']) ? trim($_POST['user_id_game']) : '';
$zone_id      = isset($_POST['zone_id']) ? trim($_POST['zone_id']) : '';
$game_type    = isset($_POST['game_type']) ? trim($_POST['game_type']) : '';
$nominal      = isset($_POST['nominal']) ? trim($_POST['nominal']) : '';
$metode       = isset($_POST['metode_pembayaran']) ? trim($_POST['metode_pembayaran']) : '';

if ($user_id_game === '' || $game_type === '' || $nominal === '' || $metode === '') {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
    exit;
}

if ($game_type === 'Mobile Legends' && $zone_id === '') {
    echo json_encode(['success' => false, 'message' => 'Zone ID wajib diisi untuk Mobile Legends.']);
    exit;
}

if (!in_array($game_type, ['Mobile Legends', 'Free Fire'])) {
    echo json_encode(['success' => false, 'message' => 'Tipe game tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO transaksi (user_id_game, zone_id, game_type, nominal, metode_pembayaran, status, tanggal) VALUES (:uid, :zid, :game, :nom, :met, 'Pending', NOW())");
    $stmt->execute([
        ':uid'  => $user_id_game,
        ':zid'  => $zone_id !== '' ? $zone_id : null,
        ':game' => $game_type,
        ':nom'  => $nominal,
        ':met'  => $metode,
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Transaksi berhasil dibuat.',
        'id'      => $newId,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi.']);
}
