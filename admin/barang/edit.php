<?php
session_start();

include '../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id = $_GET['id'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM barang WHERE id_barang='$id'"
);

$barang = mysqli_fetch_assoc($query);

if (!$barang) {
    echo "Data barang tidak ditemukan";
    exit;
}

// AMBIL KATEGORI
$queryKategori = mysqli_query(
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
    <title>Edit Barang</title>

    <style>
    body {
        font-family: system-ui, sans-serif;
    }

    .card {
        border-radius: 18px;
        overflow: hidden;
    }

    .form-control,
    .form-select {
        border-radius: 12px !important;
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
    <div class="container py-5">
        <!-- Header -->
        <div class="d-flex justify-content-center align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">✏️ Edit Barang</h3>
                <p class="text-muted mb-0">Perbarui data produk dengan benar</p>
            </div>
        </div>

        <!-- Card Form -->
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="../../proses/admin/edit/proses_edit_barang.php" method="POST">
                            <input type="hidden" name="id_barang" value="<?= $barang['id_barang']; ?>">
                            <!-- Nama -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control form-control-lg rounded-3"
                                    required value="<?= $barang['nama_barang']; ?>">
                            </div>

                            <!-- Kategori -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="id_kategori" class="form-select form-select-lg rounded-3" required>
                                    <?php while ($kategori = mysqli_fetch_assoc($queryKategori)) : ?>
                                    <option value="<?= $kategori['id_kategori']; ?>"
                                        <?= $kategori['id_kategori'] == $barang['id_kategori'] ? 'selected' : ''; ?>>
                                        <?= $kategori['nama_kategori']; ?>
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
                                    <option value="<?= $s['id_satuan']; ?>"
                                        <?= $s['id_satuan'] == $barang['id_satuan'] ? 'selected' : ''; ?>>
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
                                    <input type="number" name="harga" min="0" class="form-control" required
                                        value="<?= $barang['harga']; ?>">
                                </div>
                            </div>

                            <!-- Stok -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Stok</label>
                                <input type="number" name="stok" min="0" step="1"
                                    class="form-control form-control-lg rounded-3" required
                                    value="<?= $barang['stok']; ?>">
                            </div>

                            <!-- Button -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 flex-fill shadow-sm">
                                    💾 Simpan Perubahan
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