<?php
session_start();

include '../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$cari = $_GET['cari'] ?? '';

$limit = 15;
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$offset = ($halaman - 1) * $limit;

// Ambil Data Barang
$query = mysqli_query(
    $koneksi,
    "SELECT barang.*, 
            kategori.nama_kategori,
            satuan.nama_satuan
        FROM barang
        LEFT JOIN kategori 
            ON barang.id_kategori = kategori.id_kategori
        LEFT JOIN satuan
            ON barang.id_satuan = satuan.id_satuan
        WHERE barang.nama_barang LIKE '%$cari%'
            OR barang.harga LIKE '%$cari%'
            OR barang.stok LIKE '%$cari%'
            OR kategori.nama_kategori LIKE '%$cari%'
            -- FILTER STATUS STOK
            OR ('$cari' = 'habis' AND barang.stok = 0)
            OR ('$cari' = 'low' AND barang.stok > 0 AND barang.stok <= 5)
            OR ('$cari' = 'aman' AND barang.stok > 5)
        ORDER BY barang.id_barang DESC
        LIMIT $offset, $limit"
);

// Batas/maksimal halaman -> 15 per halaman
$total_data = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT barang.id_barang
        FROM barang
        LEFT JOIN kategori 
            ON barang.id_kategori = kategori.id_kategori
        LEFT JOIN satuan
            ON barang.id_satuan = satuan.id_satuan
        WHERE barang.nama_barang LIKE '%$cari%'
            OR barang.harga LIKE '%$cari%'
            OR barang.stok LIKE '%$cari%'
            OR kategori.nama_kategori LIKE '%$cari%'
            OR ('$cari' = 'habis' AND barang.stok = 0)
            OR ('$cari' = 'low' AND barang.stok > 0 AND barang.stok <= 5)
            OR ('$cari' = 'aman' AND barang.stok > 5)"
    )
);

$total_halaman = ceil($total_data / $limit);

$total_all = mysqli_query(
    $koneksi,
    "SELECT barang.id_barang
    FROM barang
    LEFT JOIN kategori 
        ON barang.id_kategori = kategori.id_kategori
    LEFT JOIN satuan
        ON barang.id_satuan = satuan.id_satuan
    WHERE barang.nama_barang LIKE '%$cari%'
        OR barang.harga LIKE '%$cari%'
        OR barang.stok LIKE '%$cari%'
        OR kategori.nama_kategori LIKE '%$cari%'
        OR ('$cari' = 'habis' AND barang.stok = 0)
        OR ('$cari' = 'low' AND barang.stok > 0 AND barang.stok <= 5)
        OR ('$cari' = 'aman' AND barang.stok > 5)"
);

$total_semua = mysqli_num_rows($total_all);

?>

<!-- popup hapus berhasil -->
<?php if (isset($_SESSION['success_barang'])) : ?>
<div id="alertSuccess" class="alert alert-success shadow-sm border-0">
    <?= $_SESSION['success_barang']; ?>
</div>
<?php unset($_SESSION['success_barang']); ?>
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
    <title>Kelola Barang</title>

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

    .badge-habis {
        background-color: #dc3545;
        color: white;
        font-weight: bold;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        50% {
            opacity: 0.3;
        }
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
                <h2 class="fw-bold mb-1">Kelola Barang</h2>
                <p class="text-muted mb-0">Daftar seluruh produk minimarket</p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-secondary rounded-3 px-3">
                    ← Kembali
                </a>
                <a href="tambah.php" class="btn btn-primary rounded-3 px-3 shadow-sm">
                    + Tambah Barang
                </a>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" class="mb-4" id="formCari">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-0">🔍</span>
                <input type="text" id="inputCari" name="cari" class="form-control border-0"
                    placeholder="Cari barang, kategori, harga, stok..." value="<?= htmlspecialchars($cari); ?>">
            </div>
        </form>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="fs-6">📦 Data Barang</strong>
                    <span class="text-muted small">
                        Total: <?= $total_semua; ?> item
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-scroll" id="tableScroll">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $offset + 1; while ($barang = mysqli_fetch_assoc($query)) : ?>
                            <tr>
                                <td class="ps-3"><?= $no++; ?></td>
                                <td class="fw-semibold"> <?= $barang['nama_barang']; ?> </td>
                                <td>
                                    <span class="rounded-pill">
                                        <?= $barang['nama_kategori'] ?? '-'; ?>
                                    </span>
                                </td>
                                <td class="text-success fw-semibold">
                                    Rp <?= number_format($barang['harga']); ?>
                                </td>
                                <td>
                                    <?php if ($barang['stok'] <= 0): ?>
                                    <span class="badge badge-habis rounded-pill">
                                        HABIS
                                    </span>
                                    <?php elseif ($barang['stok'] <= 5): ?>
                                    <span class="badge bg-danger rounded-pill">
                                        <?= $barang['stok']; ?> (LOW)
                                    </span>
                                    <?php elseif ($barang['stok'] <= 20): ?>
                                    <span class="badge bg-warning text-dark rounded-pill">
                                        <?= $barang['stok']; ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-success rounded-pill">
                                        <?= $barang['stok']; ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td> <?= $barang['nama_satuan'] ?? '-'; ?> </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit.php?id=<?= $barang['id_barang']; ?>"
                                            class="btn btn-sm btn-outline-warning rounded-3 px-3">
                                            ✏️
                                        </a>
                                        <a href="../../proses/admin/hapus/proses_hapus_barang.php?id=<?= $barang['id_barang']; ?>"
                                            class="btn btn-sm btn-outline-danger rounded-3 px-3"
                                            onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <div>
                                        <div style="font-size:40px;">📦</div>
                                        Tidak ada data barang
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
                <!-- Pagination halaman -->
                <div class="d-flex justify-content-center align-items-center gap-2 mt-3 mb-3">
                    <?php if ($halaman > 1): ?>
                    <a href="?cari=<?= urlencode($cari) ?>&hal=<?= $halaman - 1 ?>" class="btn btn-outline-dark">
                        ◀
                    </a>
                    <?php endif; ?>
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="cari" value="<?= htmlspecialchars($cari) ?>">
                        <span>Halaman</span>
                        <select name="hal" class="form-select" onchange="this.form.submit()" style="width:90px">
                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <option value="<?= $i ?>" <?= $halaman == $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <span class="text-muted">
                            dari <?= $total_halaman ?>
                        </span>
                    </form>
                    <?php if ($halaman < $total_halaman): ?>
                    <a href="?cari=<?= urlencode($cari) ?>&hal=<?= $halaman + 1 ?>" class="btn btn-outline-dark">
                        ▶
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function autoHideAlert(id) {
        const alertBox = document.getElementById(id);
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = "all 0.5s ease";
                alertBox.style.opacity = "0";
                alertBox.style.transform = "translateY(-10px)";

                setTimeout(() => {
                    alertBox.remove();
                }, 500);
            }, 3000);
        }
    }

    // jalankan
    autoHideAlert("alertSuccess");
    autoHideAlert("alertError");
    </script>

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

    <!-- Agar tidak kembali ke atas saat pindah halaman -->
    <script>
    /* SIMPAN POSISI SCROLL SEBELUM PINDAH HALAMAN */
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('scrollPos', window.scrollY);
    });

    /* KEMBALIKAN POSISI SCROLL SAAT HALAMAN LOAD */
    window.addEventListener('load', function() {
        const scrollPos = sessionStorage.getItem('scrollPos');

        if (scrollPos !== null) {
            // kasih delay biar layout selesai render dulu
            setTimeout(() => {
                window.scrollTo({
                    top: parseInt(scrollPos),
                    behavior: "smooth"
                });
            }, 50);
        }
    });
    </script>
</body>

</html>