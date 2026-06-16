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
    "SELECT * FROM satuan WHERE id_satuan='$id'"
);

$satuan = mysqli_fetch_assoc($query);

if (!$satuan) {
    echo "Satuan tidak ditemukan";
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
    <title>Edit Satuan</title>

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
    <div class="container py-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">✏️ Edit Satuan</h3>
                <p class="text-muted mb-0">Perbarui data satuan produk</p>
            </div>
        </div>

        <!-- Center Card -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="../../proses/admin/edit/proses_edit_satuan.php" method="POST">
                            <input type="hidden" name="id_satuan" value="<?= $satuan['id_satuan']; ?>">
                            <!-- Nama Satuan -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Satuan</label>
                                <input type="text" id="satuan" name="nama_satuan"
                                    class="form-control form-control-lg rounded-3"
                                    placeholder="Contoh: PCS / KG / LITER" required
                                    value="<?= $satuan['nama_satuan']; ?>">
                            </div>

                            <!-- Button -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-warning btn-lg rounded-3 flex-fill shadow-sm">
                                    💾 Update Satuan
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

    <!-- Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>