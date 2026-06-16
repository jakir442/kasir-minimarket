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
    "SELECT * FROM member WHERE id_member='$id'"
);

$member = mysqli_fetch_assoc($query);

if (!$member) {
    echo "Member tidak ditemukan";
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
    <title>Edit Member</title>

    <style>
    .form-control {
        border-radius: 12px !important;
        font-size: 16px;
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
                Dashboard Admin
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-1">
                            Edit Member
                        </h3>
                        <p class="text-muted mb-4">
                            Ubah data member minimarket
                        </p>
                        <form action="../../proses/admin/edit/proses_edit_member.php" method="POST">
                            <input type="hidden" name="id_member" value="<?= $member['id_member']; ?>">
                            <!-- ID Member -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Kode Member
                                </label>
                                <input disabled type="text" class="form-control" value="<?= $member['kode_member'] ?>"
                                    readonly>
                            </div>

                            <!-- Nama -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Member
                                </label>
                                <input type="text" name="nama_member" class="form-control" required
                                    value="<?= $member['nama_member']; ?>">
                            </div>

                            <!-- No HP -->
                            <div class="mb-3">
                                <label class="form-label">
                                    No HP
                                </label>
                                <input type="text" name="no_hp" class="form-control" required maxlength="12"
                                    pattern="[0-9]{12}" inputmode="numeric" value="<?= $member['no_hp']; ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <small class="text-muted">
                                    Nomor HP harus tepat 12 digit angka.
                                </small>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Status Member
                                </label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= $member['status'] == 'aktif' ? 'selected' : ''; ?>>
                                        Aktif
                                    </option>
                                    <option value="nonaktif" <?= $member['status'] == 'nonaktif' ? 'selected' : ''; ?>>
                                        Nonaktif
                                    </option>
                                </select>
                            </div>

                            <!-- Tombol -->
                            <div class="d-flex gap-2">
                                <a href="index.php" class="btn btn-secondary">
                                    ← Kembali
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    Update
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