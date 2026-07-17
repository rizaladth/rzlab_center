<?php
define('DB_HOST', 'sql207.infinityfree.com');
define('DB_NAME', 'if0_42436067_rzlabdb');
define('DB_USER', 'if0_42436067');
define('DB_PASS', 'oaLIH8UOdFY2');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'koneksi.php') {
        die("Koneksi database gagal: " . $e->getMessage());
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Koneksi database gagal.']));
    }
    die("Koneksi database gagal.");
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /rzlab_center/auth/login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles)) {
        header('Location: /rzlab_center/admin/beranda.php');
        exit;
    }
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
