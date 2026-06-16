<?php
session_start();

include '../../config/db.php';

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
    <title>Tambah Kategori</title>

    <style>
    .form-control,
    .form-select {
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
    <!-- Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1">📂 Tambah Kategori</h3>
                            <p class="text-muted mb-0">
                                Tambahkan kategori produk baru
                            </p>
                        </div>

                        <!-- Form -->
                        <form action="../../proses/admin/tambah/proses_tambah_kategori.php" method="POST">
                            <!-- Nama -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori</label>
                                <input type="text" name="nama_kategori" class="form-control form-control-lg rounded-3"
                                    placeholder="Contoh: Makanan" required>
                            </div>

                            <!-- Button -->
                            <div class="d-flex gap-2 mt-4">
                                <a href="index.php" class="btn btn-outline-secondary btn-lg rounded-3">
                                    ← Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 flex-fill shadow-sm">
                                    💾 Simpan Kategori
                                </button>
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