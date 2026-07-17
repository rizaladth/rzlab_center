<?php
/**
 * =============================================
 * RizalLab Command - API: Logout
 * =============================================
 * Menghapus session pengguna.
 */

session_start();
session_unset();
session_destroy();

header('Location: index.php');
exit;
