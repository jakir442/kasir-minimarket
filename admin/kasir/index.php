<?php
session_start();

include '../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$cari = $_GET['cari'] ?? '';

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pengguna
    WHERE role = 'kasir'
    AND (
        nama LIKE '%$cari%'
        OR username LIKE '%$cari%'
        OR password LIKE '%$cari%'
    )
    ORDER BY id_pengguna ASC"
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

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Kelola Kasir</title>

    <style>
    .table-hover tbody tr:hover {
        background: #f6f9ff;
        transition: 0.2s;
    }

    .table-scroll {
        max-height: 420px;
        /* tinggi maksimal tabel */
        overflow-y: auto;
        border-radius: 12px;
    }

    /* biar header tetap diam saat scroll */
    .table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #212529;
        /* sama dengan table-dark */
    }

    /* biar lebih halus */
    .table-scroll table {
        margin-bottom: 0;
    }

    .card {
        border-radius: 16px;
    }

    .btn {
        transition: 0.2s;
    }

    .btn:hover {
        transform: translateY(-1px);
    }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="../index.php" class="navbar-brand fw-bold text-decoration-none">
                <span class="navbar-brand fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
                </span>
            </a>
            <div class="ms-auto">
                <a href="../../auth/logout.php" class="btn btn-danger btn-sm">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">👤 Kelola Kasir</h2>
                <p class="text-muted mb-0">Daftar akun kasir minimarket</p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-secondary rounded-3 px-3">
                    ← Kembali
                </a>
                <a href="tambah.php" class="btn btn-primary rounded-3 px-3 shadow-sm">
                    + Tambah Kasir
                </a>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" class="mb-4" id="formCari">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-0">🔍</span>
                <input type="text" id="inputCari" name="cari" class="form-control border-0"
                    placeholder="Cari nama atau username..." value="<?= htmlspecialchars($cari); ?>">
            </div>
        </form>

        <!-- Card Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>👤 Data Kasir</strong>
                    <span class="text-muted small">
                        Total: <?= mysqli_num_rows($query); ?> kasir
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-scroll">
                    <table class="table table-hover align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($kasir = mysqli_fetch_assoc($query)) : ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="fw-semibold">
                                    <?= $kasir['nama']; ?>
                                </td>
                                <td>
                                    <span class="rounded-pill">
                                        <?= $kasir['username']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="rounded-pill">
                                        <?= $kasir['password']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="rounded-pill">
                                        Kasir
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit.php?id=<?= $kasir['id_pengguna']; ?>"
                                            class="btn btn-sm btn-outline-warning rounded-3 px-3">
                                            ✏️
                                        </a>
                                        <a href="../../proses/admin/hapus/proses_hapus_kasir.php?id=<?= $kasir['id_pengguna']; ?>"
                                            class="btn btn-sm btn-outline-danger rounded-3 px-3"
                                            onclick="return confirm('Yakin ingin menghapus kasir ini?')">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="5" class="py-5 text-muted">
                                    <div>
                                        <div style="font-size:40px;">👤</div>
                                        Tidak ada data kasir
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cari otomatis saat mengetik -->
    <script>
    const inputCari = document.getElementById("inputCari");
    let debounceTimer;
    inputCari.addEventListener("input", function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById("formCari").submit();
        }, 600); // tunggu 600ms atau 0.6 detik baru mencari
    });

    window.addEventListener('load', function() {
        const input = document.getElementById('inputCari');
        if (input) {
            input.focus(); // agar tetap fokus di kolom input, meski tidak ada teks
            const panjang = input.value.length;
            input.setSelectionRange(panjang, panjang);
        }
    });
    </script>

</body>

</html>