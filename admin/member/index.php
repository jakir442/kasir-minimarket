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
    "SELECT * FROM member
    WHERE nama_member LIKE '%$cari%' OR no_hp LIKE '%$cari%'
    ORDER BY id_member ASC"
);

?>

<?php if (isset($_SESSION['success_member'])) : ?>
<div id="alertSuccess" class="alert alert-success m-3">
    <?= $_SESSION['success_member']; ?>
</div>
<?php unset($_SESSION['success_member']); ?>
<?php endif; ?>

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
    <title>Kelola Member</title>

    <style>
    .form-control {
        border-radius: 12px !important;
        font-size: 16px;
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
                <h2 class="fw-bold mb-1">
                    👥 Kelola Member
                </h2>
                <p class="text-muted mb-0">
                    Daftar member minimarket
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-secondary">
                    ← Kembali
                </a>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" class="mb-4" id="formCari">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-0">
                    🔍
                </span>
                <input type="text" id="inputCari" name="cari" class="form-control border-0"
                    placeholder="Cari nama atau no hp..." value="<?= htmlspecialchars($cari); ?>">
            </div>
        </form>

        <!-- Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>
                        👥 Data Member
                    </strong>
                    <span class="text-muted small">
                        Total: <?= mysqli_num_rows($query); ?> member
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-scroll">
                    <table class="table table-hover align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="80">No</th>
                                <th>Kode Member</th>
                                <th>Nama Member</th>
                                <th>No HP</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $no = 1;
                                while ($member = mysqli_fetch_assoc($query)) :
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        <?= $member['kode_member']; ?>
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    <?= $member['nama_member']; ?>
                                </td>
                                <td><?= $member['no_hp']; ?></td>
                                <td>
                                    <?php if ($member['status'] == 'aktif') : ?>
                                    <span class="badge bg-success px-3 py-2">
                                        Aktif
                                    </span>
                                    <?php else : ?>
                                    <span class="badge bg-danger px-3 py-2">
                                        Nonaktif
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($member['tanggal_daftar'])
                                    ); ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit.php?id=<?= $member['id_member']; ?>"
                                            class="btn btn-warning btn-sm">
                                            Edit
                                        </a>
                                        <?php if ($member['status'] == 'aktif') : ?>
                                        <a href="../../proses/admin/member/proses_nonaktif_member.php?id=<?= $member['id_member']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Nonaktifkan member ini?')">
                                            Nonaktifkan
                                        </a>
                                        <?php else : ?>
                                        <a href="../../proses/admin/member/proses_aktif_member.php?id=<?= $member['id_member']; ?>"
                                            class="btn btn-success btn-sm"
                                            onclick="return confirm('Aktifkan kembali member ini?')">
                                            Aktifkan
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="6" class="py-5 text-muted">
                                    Belum ada data member
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

    <script>
    function autoHideAlert(id) {
        const alertBox = document.getElementById(id);
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = "0.5s";
                alertBox.style.opacity = "0";
                setTimeout(() => {
                    alertBox.remove();
                }, 500);
            }, 3000);
        }
    }
    autoHideAlert("alertSuccess");
    </script>
</body>

</html>