<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

$total_barang = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM barang"));
$total_kategori = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kategori"));
$total_satuan = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM satuan"));
$total_transaksi = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM transaksi"));
$total_member = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM member"));

$qPendapatan = mysqli_query($koneksi, "SELECT SUM(total_harga) as total_pendapatan FROM transaksi WHERE status='dibayar'");
$dataPendapatan = mysqli_fetch_assoc($qPendapatan);
$total_pendapatan = $dataPendapatan['total_pendapatan'] ?? 0;
$admin = $_SESSION['admin'];

// Query Best Seller (Top 5 Produk Terlaris)
$query_best_seller = mysqli_query(
    $koneksi,
    "SELECT
        barang.id_barang,
        barang.nama_barang,
        barang.stok,
        SUM(detail_transaksi.qty) AS total_terjual
    FROM detail_transaksi
    INNER JOIN barang
        ON detail_transaksi.id_barang = barang.id_barang
    GROUP BY
        barang.id_barang,
        barang.nama_barang,
        barang.stok
    ORDER BY total_terjual DESC
    LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../image/logo-website.ico">
    <!-- bootstrap 5 css  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Dashboard Admin</title>

    <style>
    body {
        background: #f4f6fb;
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

    /* Navbar modern */
    .navbar {
        backdrop-filter: blur(10px);
        background: rgba(33, 37, 41, 0.95) !important;
    }

    /* Card stat */
    .stat-card {
        border: none;
        border-radius: 18px;
        transition: 0.25s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    /* menu card */
    .menu-card {
        border: none;
        border-radius: 18px;
        transition: 0.25s;
    }

    .menu-card:hover {
        transform: scale(1.03);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    /* icon box */
    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
            </span>
            <div class="text-white d-flex align-items-center gap-3">
                <span>Halo, <b><?= $admin['nama']; ?></b></span>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- CONTAINER -->
    <div class="container py-4">
        <!-- WELCOME -->
        <div class="alert alert-primary border-0 shadow-sm rounded-4">
            👋 Selamat datang, <b><?= $admin['nama']; ?></b> sebagai <b>Admin</b>
        </div>

        <!-- STATISTIK UTAMA -->
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 h-100">
                    <div class="icon-box bg-primary text-white">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6>Total Barang</h6>
                    <h3><?= $total_barang ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card p-3 h-100">
                    <div class="icon-box bg-warning text-white">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h6>Transaksi</h6>
                    <h3><?= $total_transaksi ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card stat-card p-3 h-100">
                    <div class="icon-box bg-danger text-white">
                        <i class="bi bi-rulers"></i>
                    </div>
                    <h6>Total Satuan</h6>
                    <h3><?= $total_satuan ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card stat-card p-3 h-100">
                    <div class="icon-box bg-success text-white">
                        <i class="bi bi-tags"></i>
                    </div>
                    <h6>Kategori</h6>
                    <h3><?= $total_kategori ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card stat-card p-3 h-100">
                    <div class="icon-box bg-secondary text-white">
                        <i class="bi bi-people"></i>
                    </div>
                    <h6>Total Member</h6>
                    <h3><?= $total_member ?></h3>
                </div>
            </div>
        </div>

        <!-- Pendapatan -->
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="card stat-card p-4 text-center">
                    <div class="icon-box bg-info text-white mx-auto">
                        <i class="bi bi-cash"></i>
                    </div>
                    <h6>Total Pendapatan</h6>
                    <h2 class="text-success fw-bold">
                        Rp <?= number_format($total_pendapatan) ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <div class="mt-5">
            <h5 class="fw-bold mb-3">Menu Utama</h5>
            <div class="row g-3 justify-content-center">
                <?php
                    $menu = [
                        ["Barang","barang/index.php","bi-box"],
                        ["Kategori","kategori/index.php","bi-tags"],
                        ["Satuan","satuan/index.php","bi-rulers"],
                        ["Kasir","kasir/index.php","bi-person"],
                        ["Member","member/index.php","bi-people"],
                        ["Laporan","laporan/index.php","bi-graph-up"]
                    ];
                    foreach($menu as $m):
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $m[1] ?>" class="text-decoration-none text-dark">
                        <div class="card menu-card text-center p-4 h-100">
                            <i class="bi <?= $m[2] ?> fs-2 text-primary"></i>
                            <div class="fw-bold mt-2"><?= $m[0] ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Best Seller atau Ranking Produk -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            🔥 Top 5 Produk Terlaris
                        </h5>
                        <div class="best-seller-scroll">
                            <?php if(mysqli_num_rows($query_best_seller) > 0): ?>
                            <?php
                                $ranking = 1;
                                while($best = mysqli_fetch_assoc($query_best_seller)):
                            ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div class="fw-semibold">
                                    <?php
                            if ($ranking == 1) echo "🥇";
                            elseif ($ranking == 2) echo "🥈";
                            elseif ($ranking == 3) echo "🥉";
                            elseif ($ranking == 4) echo "4️⃣";
                            else echo "5️⃣";
                            ?>
                                    <?= htmlspecialchars($best['nama_barang']); ?>
                                </div>
                                <span class="badge bg-success">
                                    <?= number_format($best['total_terjual']); ?> Terjual
                                </span>
                            </div>
                            <?php
                                $ranking++;
                                endwhile;
                            ?>
                            <?php else: ?>
                            <div class="text-center text-muted">
                                Belum ada data penjualan
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    </script>
</body>

</html>