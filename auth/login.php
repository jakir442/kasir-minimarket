<?php
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: ../admin/index.php");
    exit;
}

if (isset($_SESSION['kasir'])) {
    header("Location: ../kasir/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../image/logo-website.ico">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Login - Kasir Minimarket</title>

    <style>
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #0d6efd, #4f46e5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border: none;
        border-radius: 25px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, .15);
        overflow: hidden;
    }

    .logo-img {
        width: 200px;
        height: 200px;
        object-fit: contain;
    }

    .logo-box {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
        color: white;
        font-size: 40px;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px;
    }

    .input-group-text {
        border-radius: 12px 0 0 12px;
        background: #f8f9fa;
    }

    .btn-login {
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, .4);
    }

    .footer-text {
        font-size: 14px;
        color: #6c757d;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="logo-box mb-4">
                            <img src="../image/logo-login-2.png" alt="Logo Minimarket" class="logo-img">
                        </div>
                        <h2 class="text-center fw-bold">
                            Selamat Datang
                        </h2>
                        <p class="text-center text-muted mb-4">
                            Login Sistem Kasir Minimarket
                        </p>
                        <form action="../proses/auth/proses_login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Username
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control"
                                        placeholder="Masukkan username" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Masukkan password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Login
                            </button>
                        </form>
                        <hr>
                        <p class="text-center footer-text mb-0">
                            KELOMPOK 1 • INFORMATIKA A
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>