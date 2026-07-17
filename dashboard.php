<?php
/**
 * =============================================
 * RizalLab Command - Dashboard
 * =============================================
 * Halaman utama setelah login berhasil.
 */
require_once __DIR__ . '/config/session.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RizalLab Command - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark dashboard-navbar">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-cpu me-2"></i>RizalLab Command
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-white"
                       role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span class="d-none d-md-inline fw-medium">
                            <?= htmlspecialchars($user['full_name']) ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text">
                                <small class="text-muted">
                                    <i class="bi bi-person-badge me-1"></i>
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="api/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-box-seam me-2"></i>Inventori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-laptop me-2"></i>Peralatan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-clipboard-data me-2"></i>Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-gear me-2"></i>Pengaturan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </h1>
                    <span class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('d F Y') ?>
                    </span>
                </div>

                <!-- Welcome Card -->
                <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                    <div>
                        Selamat datang kembali, <strong><?= htmlspecialchars($user['full_name']) ?></strong>!
                        Anda login sebagai <strong><?= ucfirst(htmlspecialchars($user['role'])) ?></strong>.
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-0">Total Item</h6>
                                        <h3 class="fw-bold mb-0">--</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-0">Tersedia</h6>
                                        <h3 class="fw-bold mb-0">--</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-0">Perlu Perbaikan</h6>
                                        <h3 class="fw-bold mb-0">--</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-0">Rusak</h6>
                                        <h3 class="fw-bold mb-0">--</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder Content -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-clipboard-data display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">Modul Inventori</h4>
                        <p class="text-muted">Halaman inventori laboratorium akan segera hadir.</p>
                        <span class="badge bg-primary">Coming Soon</span>
                    </div>
                </div>

                <p class="text-center text-muted mt-4 small">
                    &copy; 2026 RizalLab Command. Sistem Informasi Inventori Lab Komputer.
                </p>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
