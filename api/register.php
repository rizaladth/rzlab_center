<?php
/**
 * =============================================
 * RizalLab Command - API: Register
 * =============================================
 * Endpoint AJAX untuk pendaftaran akun baru.
 * Method: POST
 * Input: full_name, username, email, password, confirm_password (JSON)
 * Output: JSON response
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$full_name       = trim($input['full_name'] ?? '');
$username        = trim($input['username'] ?? '');
$email           = trim($input['email'] ?? '');
$password        = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// Validasi input
$errors = [];

if (empty($full_name) || strlen($full_name) < 3) {
    $errors[] = 'Nama lengkap minimal 3 karakter';
}

if (empty($username) || strlen($username) < 4) {
    $errors[] = 'Username minimal 4 karakter';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = 'Password minimal 6 karakter';
}

if ($password !== $confirm_password) {
    $errors[] = 'Konfirmasi password tidak cocok';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(' | ', $errors)]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Cek username duplikat
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan']);
        exit;
    }

    // Cek email duplikat
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
        exit;
    }

    // Insert user baru
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (:username, :email, :password, :full_name, 'operator')");
    $stmt->execute([
        ':username'  => $username,
        ':email'     => $email,
        ':password'  => $hashed_password,
        ':full_name' => $full_name,
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Akun berhasil dibuat! Silakan login.',
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
