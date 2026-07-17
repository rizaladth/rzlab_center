<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RizalLab Command - Lupa Password</title>
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

    <!-- Forgot Password Section -->
    <section class="login-section">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-lg-5 col-md-7 col-sm-10">

                    <div class="card login-card shadow-lg border-0">
                        <div class="card-body p-5">

                            <div class="text-center mb-4">
                                <div class="login-logo mx-auto mb-3">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <h2 class="fw-bold text-dark mb-1">Lupa Password</h2>
                                <p class="text-muted small">Masukkan email Anda untuk mereset password</p>
                            </div>

                            <!-- Alert Container -->
                            <div id="resetAlert" class="alert d-none" role="alert"></div>

                            <!-- Step 1: Request Reset -->
                            <div id="stepRequest">
                                <form id="resetRequestForm" autocomplete="off">
                                    <div class="mb-4">
                                        <label for="resetEmail" class="form-label fw-medium">
                                            <i class="bi bi-envelope me-1"></i>Email
                                        </label>
                                        <input type="email" class="form-control form-control-lg" id="resetEmail"
                                               name="email" placeholder="Masukkan email terdaftar" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="resetRequestBtn">
                                        <span class="btn-text">
                                            <i class="bi bi-send me-2"></i>Kirim Token Reset
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </form>
                            </div>

                            <!-- Step 2: Reset Password -->
                            <div id="stepReset" class="d-none">
                                <form id="resetPasswordForm" autocomplete="off">
                                    <div class="mb-3">
                                        <label for="resetToken" class="form-label fw-medium">
                                            <i class="bi bi-key me-1"></i>Token Reset
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="resetToken"
                                               name="token" placeholder="Masukkan token dari email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label fw-medium">
                                            <i class="bi bi-lock me-1"></i>Password Baru
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control form-control-lg" id="newPassword"
                                                   name="new_password" placeholder="Minimal 6 karakter" required minlength="6">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                    tabindex="-1" data-target="newPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirmNewPassword" class="form-label fw-medium">
                                            <i class="bi bi-lock-fill me-1"></i>Konfirmasi Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control form-control-lg" id="confirmNewPassword"
                                                   name="confirm_password" placeholder="Ulangi password baru" required minlength="6">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                    tabindex="-1" data-target="confirmNewPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="resetPasswordBtn">
                                        <span class="btn-text">
                                            <i class="bi bi-check-circle me-2"></i>Reset Password
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </form>
                            </div>

                            <!-- Divider -->
                            <div class="divider my-4">
                                <span>atau</span>
                            </div>

                            <div class="text-center">
                                <p class="mb-0 text-muted">
                                    <a href="index.php" class="fw-semibold text-decoration-none signup-link">
                                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
                                    </a>
                                </p>
                            </div>

                        </div>
                    </div>

                    <p class="text-center text-muted mt-4 small">
                        &copy; 2026 RizalLab Command. All rights reserved.
                    </p>

                </div>
            </div>
        </div>
    </section>

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
