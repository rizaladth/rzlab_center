<?php
/**
 * =============================================
 * RizalLab Command - Session Configuration
 * =============================================
 * Memulai dan mengamankan session.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memeriksa apakah pengguna sudah login
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect ke halaman login jika belum login
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Mendapatkan data pengguna dari session
 * @return array|null
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role'      => $_SESSION['role'] ?? '',
    ];
}
