<?php
session_start();

include 'config/db.php';
$current_url = $_SERVER['REQUEST_URI'];

// Generate ID Transaksi 4 Digit
$kode_transaksi = rand(1000, 9999);

// Ambil Data Barang
$cari = mysqli_real_escape_string($koneksi, $_GET['cari'] ?? '');

$limit = 15;
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$offset = ($halaman - 1) * $limit;

$total_data = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT barang.id_barang
        FROM barang
        LEFT JOIN kategori
            ON barang.id_kategori = kategori.id_kategori
        LEFT JOIN satuan
            ON barang.id_satuan = satuan.id_satuan
        WHERE
            barang.nama_barang LIKE '%$cari%'
            OR barang.harga LIKE '%$cari%'
            OR kategori.nama_kategori LIKE '%$cari%'
            OR satuan.nama_satuan LIKE '%$cari%'"
    )
);

$total_halaman = ceil($total_data / $limit);
$total_barang_tersedia = $total_data;

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
        WHERE 
            barang.nama_barang LIKE '%$cari%'
            OR barang.harga LIKE '%$cari%'
            OR kategori.nama_kategori LIKE '%$cari%'
            OR satuan.nama_satuan LIKE '%$cari%'
        ORDER BY barang.nama_barang ASC
        LIMIT $offset, $limit"
);

// Session keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Tambah Produk ke Keranjang
if (isset($_GET['tambah'])) {
    $id_barang = $_GET['tambah'];

    $ambil_barang = mysqli_query(
        $koneksi,
        "SELECT * FROM barang WHERE id_barang='$id_barang'"
    );

    $barang = mysqli_fetch_assoc($ambil_barang);
    if ($barang) {
        $found = false;
        foreach ($_SESSION['keranjang'] as &$item) {
            if ($item['id_barang'] == $barang['id_barang']) {
                // CEK STOK
                if ($item['qty'] >= $barang['stok']) {
                    echo "<script>
                    alert('Stok tidak mencukupi!');
                    window.location='index.php';
                </script>";
                    exit;
                }
                $item['qty']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            // CEK STOK AWAL
            if ($barang['stok'] < 1) {
                echo "<script>
                alert('Stok habis!');
                window.location='index.php';
            </script>";
                exit;
            }
            $_SESSION['keranjang'][] = [
                'id_barang' => $barang['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'harga' => $barang['harga'],
                'qty' => 1
            ];
        }
    }
    $redirect = $_GET['redirect'] ?? 'index.php';
    header("Location: $redirect");
    exit;
}

// kurangi produk
if (isset($_GET['kurangi'])) {
    $id_barang = $_GET['kurangi'];
    foreach ($_SESSION['keranjang'] as $key => $item) {
        if ($item['id_barang'] == $id_barang) {
            $_SESSION['keranjang'][$key]['qty']--;
            if ($_SESSION['keranjang'][$key]['qty'] <= 0) {
                unset($_SESSION['keranjang'][$key]);
                $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
            }
            break;
        }
    }
    $redirect = $_GET['redirect'] ?? 'index.php';
    header("Location: $redirect");
    exit;
}

// Hitung Total
$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['harga'] * $item['qty'];
}

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

// Kode transaksi berhasil dibuat
$showModal = false;
if (isset($_GET['success']) && isset($_SESSION['kode_transaksi_berhasil'])) {
    $showModal = true;
    $kode_transaksi = $_SESSION['kode_transaksi_berhasil'];
    unset($_SESSION['kode_transaksi_berhasil']);
}

if (isset($_GET['reset'])) {
    unset($_SESSION['keranjang']);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="image/logo-website.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Minimarket Viora</title>

    <style>
    .detail-transaksi-scroll {
        height: 180px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .produk-scroll {
        max-height: 550px;
        overflow-y: auto;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .best-seller-scroll {
        max-height: 300px;
        overflow-y: auto;
    }

    @media (min-width:992px) {

        .produk-scroll {
            max-height: 650px;
        }

        .detail-transaksi-card {
            position: sticky;
            top: 20px;
        }
    }

    /* Header tabel tetap di atas */
    .produk-scroll thead th {
        position: sticky;
        top: -2px;
        z-index: 10;
        background: #cfe2ff;
    }

    .produk-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .produk-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .produk-scroll::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
    }

    .produk-scroll::-webkit-scrollbar-thumb:hover {
        background: #0b5ed7;
    }

    /* Scrollbar modern */
    .detail-transaksi-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .detail-transaksi-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .detail-transaksi-scroll::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
    }

    .detail-transaksi-scroll::-webkit-scrollbar-thumb:hover {
        background: #0b5ed7;
    }

    /* BEST SELLER */
    .best-seller-scroll {
        max-height: 270px;
        overflow-y: auto;
    }

    .best-seller-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .best-seller-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .best-seller-scroll::-webkit-scrollbar-thumb {
        background: #198754;
        border-radius: 10px;
    }

    .best-seller-scroll::-webkit-scrollbar-thumb:hover {
        background: #146c43;
    }

    .struk-items-scroll {
        height: 100px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .struk-items-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .struk-items-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .struk-items-scroll::-webkit-scrollbar-thumb {
        background: #198754;
        border-radius: 10px;
    }

    .struk-items-scroll::-webkit-scrollbar-thumb:hover {
        background: #146c43;
    }


    /* Desktop besar */
    @media (min-width: 1200px) {
        .table {
            min-width: 100%;
        }
    }

    /* Laptop kecil & Tablet */
    @media (max-width: 1199px) {
        .table {
            min-width: 900px;
        }
    }

    /* HP atau Mobile */
    @media (max-width: 768px) {
        .table {
            min-width: 800px;
            font-size: 12px;
        }

        .btn-sm {
            padding: 2px 6px;
        }
    }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                Minimarket Viora
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
        <div class="row">
            <!-- Produk -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold mb-0">
                                Daftar Produk
                            </h3>
                            <div class="text-end">
                                <div class="small text-muted">
                                    Produk Tersedia
                                </div>
                                <div class="fw-bold text-primary fs-5">
                                    <?= number_format($total_barang_tersedia); ?> Item
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <form method="GET" class="mb-4" id="formCari">
                            <input type="text" id="inputCari" name="cari" class="form-control"
                                placeholder="Cari nomor, nama barang, atau harga..."
                                value="<?= htmlspecialchars($cari); ?>">
                        </form>

                        <!-- Table Barang -->
                        <div class="table-responsive produk-scroll">
                            <table class="table table-bordered align-middle text-nowrap">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Kategori</th>
                                        <th>Satuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = $offset + 1;
                                    while ($barang = mysqli_fetch_assoc($query)) :
                                        $id_barang = $barang['id_barang'];
                                        // cari qty di keranjang
                                        $qty_di_keranjang = 0;
                                        if (!empty($_SESSION['keranjang'])) {
                                            foreach ($_SESSION['keranjang'] as $item) {
                                                if ($item['id_barang'] == $id_barang) {
                                                    $qty_di_keranjang = $item['qty'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <!-- NO -->
                                        <td>
                                            <?= $no++ ; ?>
                                        </td>
                                        <!-- NAMA -->
                                        <td>
                                            <?= $barang['nama_barang']; ?>
                                        </td>
                                        <!-- HARGA -->
                                        <td>
                                            Rp <?= number_format($barang['harga']); ?>
                                        </td>
                                        <!-- STOK -->
                                        <td>
                                            <?= $barang['stok']; ?>
                                        </td>
                                        <!-- Kategori -->
                                        <td>
                                            <?= $barang['nama_kategori']; ?>
                                        </td>
                                        <!-- Satuan -->
                                        <td><?= $barang['nama_satuan'] ?? '-'; ?></td>
                                        <!-- AKSI -->
                                        <td class="text-center">
                                            <?php if ($barang['stok'] <= 0) : ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                Habis
                                            </button>
                                            <?php else : ?>
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                <?php if ($qty_di_keranjang >= $barang['stok']) : ?>
                                                <!-- MAX (tidak bisa klik) -->
                                                <button class="btn btn-dark btn-sm" disabled>
                                                    MAX
                                                </button>
                                                <?php else : ?>
                                                <!-- Tambah -->
                                                <a href="?tambah=<?= $id_barang; ?>&redirect=<?= urlencode($current_url) ?>"
                                                    class="btn btn-success btn-sm">
                                                    +
                                                </a>
                                                <?php endif; ?>
                                                <!-- Tombol Minus (selalu aktif kalau sudah ada di keranjang) -->
                                                <a href="?kurangi=<?= $id_barang; ?>&redirect=<?= urlencode($current_url) ?>"
                                                    class="btn btn-warning btn-sm">
                                                    -
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Halaman -->
                        <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
                            <?php if($halaman > 1): ?>
                            <a href="?cari=<?= urlencode($cari) ?>&hal=<?= $halaman-1 ?>"
                                class="btn btn-outline-primary">
                                ◀
                            </a>
                            <?php endif; ?>
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="cari" value="<?= htmlspecialchars($cari) ?>">
                                <label class="fw-semibold">
                                    Halaman
                                </label>
                                <select name="hal" class="form-select" onchange="this.form.submit()" style="width:90px">
                                    <?php for($i=1; $i<=$total_halaman; $i++): ?>
                                    <option value="<?= $i ?>" <?= $halaman==$i ? 'selected' : '' ?>>
                                        <?= $i ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                                <span class="text-muted">
                                    dari <?= $total_halaman ?>
                                </span>
                            </form>
                            <?php if($halaman < $total_halaman): ?>
                            <a href="?cari=<?= urlencode($cari) ?>&hal=<?= $halaman+1 ?>"
                                class="btn btn-outline-primary">
                                ▶
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Transaksi -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h4 class="fw-bold mb-4">
                            Detail Transaksi
                        </h4>
                        <?php if (!empty($_SESSION['keranjang'])) : ?>
                        <div class="detail-transaksi-scroll">
                            <?php foreach (array_reverse($_SESSION['keranjang']) as $item) : ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">
                                            <?= $item['nama_barang']; ?>
                                        </div>
                                        <small class="text-muted">
                                            Rp <?= number_format($item['harga']); ?>
                                        </small>
                                        <div class="small text-primary">
                                            Subtotal :
                                            Rp <?= number_format(
                                                $item['harga'] * $item['qty']
                                            ); ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="?kurangi=<?= $item['id_barang']; ?>&redirect=<?= urlencode($current_url) ?>"
                                            class="btn btn-warning btn-sm">
                                            -
                                        </a>
                                        <span class="fw-bold">
                                            <?= $item['qty']; ?>
                                        </span>
                                        <a href="?tambah=<?= $item['id_barang']; ?>&redirect=<?= urlencode($current_url) ?>"
                                            class="btn btn-success btn-sm">
                                            +
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="alert alert-light border text-center">
                            Belum ada produk dipilih
                        </div>
                        <?php endif; ?>
                        <!-- Total -->
                        <div class="d-flex justify-content-between mt-4">
                            <h5 class="fw-bold">
                                Total
                            </h5>
                            <h5 class="text-primary fw-bold">
                                Rp <?= number_format($total); ?>
                            </h5>
                        </div>
                        <!-- Tombol Kosongkan -->
                        <?php if (!empty($_SESSION['keranjang'])) : ?>
                        <div class="d-grid mt-3">
                            <a href="?reset=1" class="btn btn-outline-danger">
                                Kosongkan Keranjang
                            </a>
                        </div>
                        <?php endif; ?>
                        <!-- Informasi -->
                        <div class="alert alert-warning mt-4 mb-0">
                            Silakan periksa kembali barang sebelum memproses transaksi.
                        </div>
                        <!-- Tombol Proses -->
                        <div class="d-grid mt-4">
                            <?php if (!empty($_SESSION['keranjang'])) : ?>
                            <form action="proses/pembeli/proses_simpan_transaksi.php" method="POST" class="d-grid">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    Proses Transaksi
                                </button>
                            </form>
                            <?php else : ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                Proses Transaksi
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- modal kode transaksi -->
                <?php if(isset($_SESSION['struk'])) : ?>
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            🧾 Struk Terakhir
                        </h5>
                        <div id="printArea">
                            <div class="text-center">
                                <h6 class="fw-bold mb-0">
                                    MINIMARKET - KELOMPOK 1
                                </h6>
                                <small>
                                    <?= $_SESSION['struk']['tanggal']; ?>
                                    <?= $_SESSION['struk']['jam']; ?>
                                </small>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>
                                    Kode :
                                    <?= $_SESSION['struk']['kode']; ?>
                                </strong>
                            </div>
                            <div class="struk-items-scroll">
                                <?php foreach($_SESSION['struk']['items'] as $item) : ?>
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <?= $item['nama_barang']; ?>
                                        x<?= $item['qty']; ?>
                                    </span>
                                    <span>
                                        Rp <?= number_format($item['harga'] * $item['qty']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>TOTAL</span>
                                <span>
                                    Rp <?= number_format($_SESSION['struk']['total'] ?? 0); ?>
                                </span>
                            </div>
                        </div>
                        <button onclick="printStruk()" class="btn btn-success w-100 mt-3">
                            🖨 Cetak Struk
                        </button>
                    </div>
                </div>
                <?php endif; ?>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script print struk -->
        <script>
        function printStruk() {
            let isi = `
                    <div style="text-align:center">
                        <h3 style="margin:0">MINIMARKET VIORA</h3>
                        <small>
                            <?= $_SESSION['struk']['tanggal']; ?>
                            <?= $_SESSION['struk']['jam']; ?>
                        </small>
                    </div>
                    <hr>
                    <p>
                        <strong>Kode:</strong>
                        <?= $_SESSION['struk']['kode']; ?>
                    </p>
                    <table width="100%" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr>
                                <th align="left">Barang</th>
                                <th align="center">Qty</th>
                                <th align="right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($_SESSION['struk']['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                                <td align="center"><?= $item['qty']; ?></td>
                                <td align="right">
                                    Rp <?= number_format($item['harga'] * $item['qty']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                    <table width="100%">
                        <tr>
                            <td><strong>TOTAL</strong></td>
                            <td align="right">
                                <strong>
                                    Rp <?= number_format($_SESSION['struk']['total'] ?? 0); ?>
                                </strong>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <div style="text-align:center">
                        Terima Kasih<br>
                        Selamat Berbelanja
                    </div>
                `;
            let win = window.open('', '', 'width=400,height=700');
            win.document.write(`
                    <html>
                    <head>
                        <title>Struk</title>
                        <style>
                            body{
                                font-family: monospace;
                                width: 300px;
                                margin: auto;
                                padding: 10px;
                                font-size: 12px;
                            }
                            table{
                                width:100%;
                                border-collapse:collapse;
                            }
                            th{
                                border-bottom:1px dashed #000;
                                padding-bottom:4px;
                            }
                            td{
                                padding:3px 0;
                            }
                            hr{
                                border:none;
                                border-top:1px dashed #000;
                            }
                        </style>
                    </head>
                    <body>
                        ${isi}
                    </body>
                    </html>
                `);
            win.document.close();
            setTimeout(() => {
                win.print();
                win.close();
            }, 300);
        }
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

        <!--Untuk table agar posisi scroll tetap disana, tidak kembali keatas  -->
        <script>
        const produkScroll = document.querySelector('.produk-scroll');
        if (produkScroll) {
            const savedPos = localStorage.getItem('produkScroll');
            if (savedPos) {
                produkScroll.scrollTop = savedPos;
            }
            produkScroll.addEventListener('scroll', function() {
                localStorage.setItem(
                    'produkScroll',
                    produkScroll.scrollTop
                );
            });
        }
        </script>

        <!--Untuk halaman agar saat klick tambah tidak kembali ke atas  -->
        <script>
        // Simpan posisi halaman sebelum pindah
        window.addEventListener('beforeunload', function() {
            localStorage.setItem('scrollY', window.scrollY);
        });

        // Kembalikan posisi setelah reload
        window.addEventListener('load', function() {
            const scrollY = localStorage.getItem('scrollY');

            if (scrollY !== null) {
                setTimeout(() => {
                    window.scrollTo(0, parseInt(scrollY));
                }, 50);
            }
        });
        </script>

        <!-- Script Chatbot Viora AI -->
        <script src="https://viora-ai-widget.vercel.app/widget.js"
            data-organization-id="org_32P2JwgPTh0TqOnVkrurSL7iaQK"></script>
</body>

</html>