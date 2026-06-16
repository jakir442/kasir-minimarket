<?php
session_start();

include '../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Ambil Data Kategori
$kategori = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

// Ambil Data satuan
$satuan = mysqli_query(
    $koneksi,
    "SELECT * FROM satuan ORDER BY nama_satuan ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../image/logo-website.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Tambah Barang</title>

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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold">
                Kelola Barang
            </a>
            <a href="../../auth/logout.php" class="btn btn-danger btn-sm">
                Logout
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1">📦 Tambah Barang</h3>
                            <p class="text-muted mb-0">
                                Tambahkan produk baru ke sistem minimarket
                            </p>
                        </div>

                        <!-- Form -->
                        <form action="../../proses/admin/tambah/proses_tambah_barang.php" method="POST">
                            <!-- Nama -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control form-control-lg rounded-3"
                                    placeholder="Contoh: Indomie Goreng" required>
                            </div>

                            <!-- Kategori -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="id_kategori" class="form-select form-select-lg rounded-3" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php while ($k = mysqli_fetch_assoc($kategori)) : ?>
                                    <option value="<?= $k['id_kategori']; ?>">
                                        <?= $k['nama_kategori']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Satuan -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Satuan</label>
                                <select name="id_satuan" class="form-select form-select-lg rounded-3" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    <?php while ($s = mysqli_fetch_assoc($satuan)) : ?>
                                    <option value="<?= $s['id_satuan']; ?>">
                                        <?= $s['nama_satuan']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Harga -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Harga</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Stok -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Stok</label>
                                <input type="number" name="stok" class="form-control form-control-lg rounded-3"
                                    placeholder="Jumlah stok" required>
                            </div>

                            <!-- Button -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 flex-fill shadow-sm">
                                    💾 Simpan Barang
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