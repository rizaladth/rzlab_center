<?php
/**
 * =============================================
 * RizalLab Command - Setup / Installer
 * =============================================
 * Jalankan file ini sekali untuk membuat database,
 * tabel, dan akun admin default.
 *
 * Akses: http://localhost/rizallab-command/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>RizalLab Command - Setup</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css' rel='stylesheet'>";
echo "</head><body class='bg-light'><div class='container py-5'>";
echo "<h2 class='mb-4'><i class='bi bi-cpu me-2'></i>RizalLab Command - Setup Installer</h2>";

try {
    // Koneksi tanpa database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Buat database
    echo "<div class='card mb-3'><div class='card-body'>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `rizallab_inventory` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `rizallab_inventory`");
    echo "<p class='text-success'><i class='bi bi-check-circle me-1'></i>Database <strong>rizallab_inventory</strong> berhasil dibuat/diperiksa.</p>";
    echo "</div></div>";

    // 2. Buat tabel users
    echo "<div class='card mb-3'><div class='card-body'>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'operator', 'viewer') NOT NULL DEFAULT 'operator',
            `reset_token` VARCHAR(64) DEFAULT NULL,
            `reset_token_expiry` DATETIME DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `last_login` DATETIME DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<p class='text-success'><i class='bi bi-check-circle me-1'></i>Tabel <strong>users</strong> berhasil dibuat/diperiksa.</p>";
    echo "</div></div>";

    // 3. Insert admin default jika belum ada
    echo "<div class='card mb-3'><div class='card-body'>";
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@rizallab.local', $hash, 'Administrator', 'admin']);

    if ($stmt->rowCount() > 0) {
        echo "<p class='text-success'><i class='bi bi-check-circle me-1'></i>Akun <strong>admin</strong> berhasil dibuat (Password: <code>admin123</code>).</p>";
    } else {
        echo "<p class='text-info'><i class='bi bi-info-circle me-1'></i>Akun <strong>admin</strong> sudah ada, dilewati.</p>";
    }
    echo "</div></div>";

    // Selesai
    echo "<div class='alert alert-success mt-4'>";
    echo "<h5><i class='bi bi-check-all me-2'></i>Setup Selesai!</h5>";
    echo "<p>Semua komponen berhasil dipasang. Anda bisa menghapus file <code>setup.php</code> ini untuk keamanan.</p>";
    echo "<a href='index.php' class='btn btn-primary'><i class='bi bi-box-arrow-in-right me-2'></i>Menuju Login</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5><i class='bi bi-x-circle me-2'></i>Setup Gagal</h5>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='mb-0'>Pastikan MySQL sudah berjalan dan kredential koneksi benar.</p>";
    echo "</div>";
}

echo "</div></body></html>";
