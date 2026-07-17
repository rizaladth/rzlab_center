<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RizalLab Command - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-cpu me-2"></i>RizalLab Command
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="login-section">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-lg-5 col-md-7 col-sm-10">

                    <!-- Login Card -->
                    <div class="card login-card shadow-lg border-0">
                        <div class="card-body p-5">

                            <!-- Logo & Title -->
                            <div class="text-center mb-4">
                                <div class="login-logo mx-auto mb-3">
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <h2 class="fw-bold text-dark mb-1">RizalLab Command</h2>
                                <p class="text-muted small">Sistem Inventori Lab Komputer</p>
                            </div>

                            <!-- Alert Container (for AJAX messages) -->
                            <div id="loginAlert" class="alert d-none" role="alert"></div>

                            <!-- Login Form -->
                            <form id="loginForm" autocomplete="off">
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-medium">
                                        <i class="bi bi-person me-1"></i>Username
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username"
                                           name="username" placeholder="Masukkan username" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-medium">
                                        <i class="bi bi-lock me-1"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password"
                                               name="password" placeholder="Masukkan password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                                tabindex="-1" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label small" for="rememberMe">Ingat saya</label>
                                    </div>
                                    <a href="forgot_password.php" class="small text-decoration-none forgot-link">
                                        Lupa Password?
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="loginBtn">
                                    <span class="btn-text">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                    </span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </form>

                            <!-- Divider -->
                            <div class="divider my-4">
                                <span>atau</span>
                            </div>

                            <!-- Sign Up Link -->
                            <div class="text-center">
                                <p class="mb-0 text-muted">
                                    Belum punya akun?
                                    <a href="register.php" class="fw-semibold text-decoration-none signup-link">
                                        Daftar Akun
                                    </a>
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Footer text -->
                    <p class="text-center text-muted mt-4 small">
                        &copy; 2026 RizalLab Command. All rights reserved.
                    </p>

                </div>
            </div>
        </div>
    </section>

    <!-- Animated Background Particles -->
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
