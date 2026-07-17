<?php
session_start();
require 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = :user LIMIT 1");
    $stmt->execute([':user' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        $_SESSION['logged_in']  = true;

        echo json_encode(['success' => true, 'message' => 'Login berhasil.']);
    } elseif ($admin && $password === $admin['password']) {
        // Fallback: cek password teks biasa (demo)
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        $_SESSION['logged_in']  = true;

        echo json_encode(['success' => true, 'message' => 'Login berhasil.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username atau password salah.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
}
