<?php
require 'koneksi.php';
header('Content-Type: application/json');

$game = isset($_GET['game']) ? trim($_GET['game']) : '';

if ($game === '') {
    echo json_encode(['success' => false, 'message' => 'Parameter game tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, game_type, nominal, harga FROM nominal_topup WHERE game_type = :game ORDER BY harga ASC");
    $stmt->execute([':game' => $game]);
    $data = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memuat nominal.']);
}
