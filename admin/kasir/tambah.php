<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../image/logo-website.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Tambah Kasir</title>

    <style>
    .form-control {
        border-radius: 12px !important;
    }

    .card {
        border-radius: 18px;
    }

    .btn {
        transition: 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
    }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="../index.php" class="navbar-brand fw-bold">
                Dashboard Admin
            </a>
            <a href="../../auth/logout.php" class="btn btn-danger btn-sm">
                Logout
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1">👤 Tambah Kasir</h3>
                            <p class="text-muted mb-0">
                                Buat akun kasir baru untuk sistem
                            </p>
                        </div>

                        <!-- Form -->
                        <form action="../../proses/admin/tambah/proses_tambah_kasir.php" method="POST">
                            <!-- Nama -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kasir</label>
                                <input type="text" name="nama" class="form-control form-control-lg rounded-3"
                                    placeholder="Masukkan nama kasir" required>
                            </div>

                            <!-- Username -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control form-control-lg rounded-3"
                                    placeholder="Username login" required>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="text" name="password" class="form-control form-control-lg rounded-3"
                                    placeholder="Password" required>
                                <small class="text-muted">
                                    Gunakan password yang kuat
                                </small>
                            </div>

                            <!-- Button -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 flex-fill shadow-sm">
                                    💾 Simpan Kasir
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary btn-lg rounded-3">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>