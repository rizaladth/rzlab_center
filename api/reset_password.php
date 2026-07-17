<?php
/**
 * =============================================
 * RizalLab Command - API: Reset Password
 * =============================================
 * Endpoint AJAX untuk reset password.
 * 1. Request reset: kirim email/username -> generate token
 * 2. Reset password: token + new password -> update password
 * Method: POST
 * Input: action ('request' | 'reset'), email, token, new_password
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
$action = $input['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->connect();

    // =============================================
    // Action: request - Meminta reset password
    // =============================================
    if ($action === 'request') {
        $email = trim($input['email'] ?? '');

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email harus diisi']);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = :email AND is_active = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Tetap return success untuk keamanan (jangan bocorkan email)
            echo json_encode([
                'status'  => 'success',
                'message' => 'Jika email terdaftar, instruksi reset password telah dikirim.',
            ]);
            exit;
        }

        // Generate token reset
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
        $stmt->execute([
            ':token'  => $token,
            ':expiry' => $expiry,
            ':id'     => $user['id'],
        ]);

        // Dalam produksi, kirim email di sini. Untuk demo, tampilkan token.
        echo json_encode([
            'status'  => 'success',
            'message' => 'Jika email terdaftar, instruksi reset password telah dikirim.',
            'debug_token' => $token, // Hapus baris ini di production
        ]);
        exit;
    }

    // =============================================
    // Action: reset - Melakukan reset password
    // =============================================
    if ($action === 'reset') {
        $token        = trim($input['token'] ?? '');
        $new_password = $input['new_password'] ?? '';
        $confirm      = $input['confirm_password'] ?? '';

        if (empty($token) || empty($new_password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token dan password baru harus diisi']);
            exit;
        }

        if (strlen($new_password) < 6) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter']);
            exit;
        }

        if ($new_password !== $confirm) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok']);
            exit;
        }

        // Verifikasi token
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() AND is_active = 1 LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token tidak valid atau sudah kedaluwarsa']);
            exit;
        }

        // Update password dan hapus token
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
        $stmt->execute([
            ':password' => $hashed,
            ':id'       => $user['id'],
        ]);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Password berhasil direset! Silakan login dengan password baru.',
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Action tidak valid']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
