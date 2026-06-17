<?php
session_start();

include '../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../auth/login.php");
    exit;
}

//  Filter Tanggal
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

if ($tanggal_awal && $tanggal_akhir) {
    $where = "WHERE DATE(tanggal) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}

$where = "";

if ($tanggal_awal && $tanggal_akhir) {
    $where = "WHERE DATE(transaksi.tanggal) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}

// Ambil data laporan transaksi
$query = mysqli_query(
    $koneksi,
    "SELECT transaksi.*,
        member.kode_member,
        member.nama_member,
        pengguna.nama AS nama_kasir
    FROM transaksi
    LEFT JOIN member
        ON transaksi.id_member = member.id_member
    LEFT JOIN pengguna
        ON transaksi.id_pengguna = pengguna.id_pengguna
    ORDER BY transaksi.id_transaksi ASC"
);

//  Statistik
$total_pendapatan = 0;
$total_transaksi = 0;
$total_dibayar = 0;

$data_laporan = [];

while ($laporan = mysqli_fetch_assoc($query)) {
    $data_laporan[] = $laporan;
    $total_pendapatan += $laporan['total_harga'];
    $total_transaksi++;
    if ($laporan['status'] == 'dibayar') {
        $total_dibayar++;
    }
}

// Grafik metode pembayaran
$qMetode = mysqli_query(
    $koneksi,
    "SELECT metode_pembayaran, COUNT(*) as total
        FROM transaksi
        WHERE status='dibayar'
        GROUP BY metode_pembayaran"
);

$data_chart = [];

while ($row = mysqli_fetch_assoc($qMetode)) {
    $data_chart[] = $row;
}

// Grafik kasir
$qKasir = mysqli_query(
    $koneksi,
    "SELECT pengguna.nama, COUNT(transaksi.id_transaksi) as total
        FROM transaksi
        JOIN pengguna
        ON transaksi.id_pengguna = pengguna.id_pengguna
        WHERE transaksi.status='dibayar'
        GROUP BY transaksi.id_pengguna"
);

// Grafik pendapatan
$qPendapatan = mysqli_query(
    $koneksi,
    "SELECT DATE(tanggal) as tanggal, SUM(total_harga) as total
        FROM transaksi
        WHERE status='dibayar'
        GROUP BY DATE(tanggal)"
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
    <title>Laporan Transaksi</title>

    <style>
    .table tbody tr:hover {
        background: #f8f9fa;
        transition: 0.2s;
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

    @media screen {
        .table-scroll {
            max-height: 420px;
            overflow-y: auto;
        }
    }

    @media print {
        .table-scroll {
            max-height: none !important;
            overflow: visible !important;
        }

        #laporanPDF {
            width: 100%;
        }

        table {
            width: 100% !important;
            border-collapse: collapse;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .no-print {
            display: none !important;
        }
    }

    #laporanPDF {
        width: 100%;
    }

    #laporanPDF table {
        width: 100%;
        border-collapse: collapse;
    }

    /* ini penting agar tabel bisa lanjut ke halaman berikutnya */
    #laporanPDF tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    #laporanPDF thead {
        display: table-header-group;
    }

    #laporanPDF tfoot {
        display: table-footer-group;
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
                <h2 class="fw-bold mb-1">📊 Laporan Transaksi</h2>
                <p class="text-muted mb-0">Ringkasan transaksi minimarket</p>
            </div>
            <div class="d-flex gap-2 no-print">
                <a href="../index.php" class="btn btn-outline-secondary">
                    ← Kembali
                </a>
                <button onclick="downloadPDF()" class="btn btn-danger">
                    ⬇ Download PDF
                </button>
            </div>
        </div>
        <div id="laporanPDF">
            <!-- Filter -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <form method="GET" class="no-print">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">
                                    Tanggal Awal
                                </label>
                                <input type="date" name="tanggal_awal" class="form-control"
                                    value="<?= $tanggal_awal; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    Tanggal Akhir
                                </label>
                                <input type="date" name="tanggal_akhir" class="form-control"
                                    value="<?= $tanggal_akhir; ?>">
                            </div>
                            <div class="col-md-4 d-grid">
                                <button class="btn btn-primary">
                                    Filter Laporan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 no-print">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Total Pendapatan</h6>
                                    <h3 class="fw-bold text-success">
                                        Rp <?= number_format($total_pendapatan); ?>
                                    </h3>
                                </div>
                                <div style="font-size:30px;">💰</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Total Transaksi</h6>
                                    <h3 class="fw-bold">
                                        <?= $total_transaksi; ?>
                                    </h3>
                                </div>
                                <div style="font-size:30px;">🧾</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Dibayar</h6>
                                    <h3 class="fw-bold text-primary">
                                        <?= $total_dibayar; ?>
                                    </h3>
                                </div>
                                <div style="font-size:30px;">✅</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">📈 Grafik Laporan Transaksi</h5>
                    <div class="row g-4">
                        <!-- Pie -->
                        <div class="col-md-4 text-center px-5">
                            <h6 class="fw-bold mb-3">
                                Metode Pembayaran
                            </h6>
                            <div style="max-width:300px; height:300px; margin:auto;">
                                <canvas id="chartMetode"></canvas>
                            </div>
                        </div>

                        <!-- Bar -->
                        <div class="col-md-4 text-center px-5">
                            <h6 class="fw-bold mb-3">
                                Transaksi Kasir
                            </h6>
                            <div style="height:300px;">
                                <canvas id="chartKasir"></canvas>
                            </div>
                        </div>

                        <!-- Line -->
                        <div class="col-md-4 text-center px-5">
                            <h6 class="fw-bold mb-3">
                                Pendapatan
                            </h6>
                            <div style="overflow-x:auto;">
                                <div style="height:300px;">
                                    <canvas id="chartPendapatan"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-scroll">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kode</th>
                                    <th>Kasir</th>
                                    <th>Total</th>
                                    <th>Detail</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                            $no = 1;
                            foreach ($data_laporan as $laporan) :
                            ?>
                                <tr>
                                    <!-- Nomor -->
                                    <td>
                                        <?= $no++; ?>
                                    </td>
                                    <!-- Tanggal -->
                                    <td>
                                        <?= date(
                                            'd M Y H:i',
                                            strtotime($laporan['tanggal'])
                                        ); ?>
                                    </td>
                                    <!-- Kode Transaksi -->
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= $laporan['kode_transaksi']; ?>
                                        </span>
                                    </td>
                                    <!-- Kasir -->
                                    <td>
                                        <?= $laporan['nama_kasir'] ?? '-'; ?>
                                    </td>
                                    <!-- Total -->
                                    <td>
                                        Rp <?= number_format($laporan['total_harga']); ?>
                                    </td>
                                    <!-- Detail barang yang dibeli -->
                                    <td>
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#detail<?= $laporan['kode_transaksi']; ?>">
                                            Detail
                                        </button>
                                    </td>
                                    <!-- Metode Pembayaran -->
                                    <td>
                                        <?=
                                        $laporan['metode_pembayaran']
                                            ? ucfirst($laporan['metode_pembayaran'])
                                            : '-';
                                    ?>
                                    </td>
                                    <!-- Status -->
                                    <td>
                                        <?php if ($laporan['status'] == 'dibayar') : ?>
                                        <span class="badge bg-success">
                                            Dibayar
                                        </span>
                                        <?php else : ?>
                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($data_laporan) == 0) : ?>
                                <tr>
                                    <td colspan="5" class="text-muted py-4">
                                        Tidak ada data laporan
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php foreach ($data_laporan as $laporan) : ?>
                        <?php
                            $kode = $laporan['kode_transaksi'];

                            $detail = mysqli_query(
                                $koneksi,
                                "SELECT detail_transaksi.*, barang.nama_barang
                                FROM detail_transaksi
                                JOIN barang
                                    ON detail_transaksi.id_barang = barang.id_barang
                                WHERE detail_transaksi.kode_transaksi='$kode'"
                            );
                        ?>
                        <div class="modal fade" id="detail<?= $laporan['kode_transaksi']; ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Detail Transaksi
                                            <?= $laporan['kode_transaksi']; ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- INFO TRANSAKSI -->
                                        <table class="table table-bordered table-sm mb-4">
                                            <tr>
                                                <th width="180">Kasir</th>
                                                <td><?= $laporan['nama_kasir']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Kode Member</th>
                                                <td>
                                                    <?= !empty($laporan['kode_member']) ? $laporan['kode_member'] : '-'; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Nama Member</th>
                                                <td>
                                                    <?= !empty($laporan['nama_member']) ? $laporan['nama_member'] : '-'; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Metode Pembayaran</th>
                                                <td>
                                                    <?= ucfirst($laporan['metode_pembayaran']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Diskon</th>
                                                <td class="text-danger">
                                                    Rp <?= number_format($laporan['diskon']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Dibayar</th>
                                                <td class="text-primary fw-bold">
                                                    Rp <?= number_format($laporan['bayar']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Kembalian</th>
                                                <td class="text-info fw-bold">
                                                    Rp <?= number_format($laporan['kembalian']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total Bayar</th>
                                                <td class="fw-bold text-success">
                                                    Rp <?= number_format($laporan['total_harga']); ?>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- DETAIL BARANG -->
                                        <div style="max-height:300px; overflow-y:auto;">
                                            <table class="table table-bordered align-middle">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Nama Barang</th>
                                                        <th>Harga</th>
                                                        <th>Qty</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($item = mysqli_fetch_assoc($detail)) : ?>
                                                    <tr>
                                                        <td><?= $item['nama_barang']; ?></td>
                                                        <td>Rp <?= number_format($item['harga']); ?></td>
                                                        <td><?= $item['qty']; ?></td>
                                                        <td>Rp <?= number_format($item['subtotal']); ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js?v=2"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels?v=2"></script>

    <!-- Script Download PDF -->
    <script>
    document.querySelector('.table-scroll').style.maxHeight = "none";
    document.querySelector('.table-scroll').style.overflow = "visible";

    function downloadPDF() {
        // kasih waktu chart finish render
        setTimeout(() => {
            const element = document.getElementById('laporanPDF');
            html2pdf().from(element).set({
                margin: 10,
                filename: 'laporan-transaksi.pdf',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    scrollY: 0,
                    allowTaint: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'landscape'
                }
            }).save();

        }, 1000); // ⬅ ini kunci utama
    }
    </script>

    <!-- Script Grafik -->
    <script>
    // PIE Metode Pembayaran
    new Chart(document.getElementById('chartMetode'), {
        type: 'pie',
        data: {
            labels: [
                <?php
                    foreach ($data_chart as $d) {
                        echo "'" . ucfirst($d['metode_pembayaran']) . "',";
                    }
                ?>
            ],
            datasets: [{
                data: [
                    <?php
                        foreach ($data_chart as $d) {
                            echo $d['total'] . ",";
                        }
                    ?>
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                datalabels: {
                    color: '#fff',
                    offset: 10,
                    font: {
                        weight: 'bold',
                        size: 18
                    },
                    formatter: (value, context) => {
                        return context.chart.data.labels[context.dataIndex] + ': ' + value;
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 2500,
                easing: 'easeInOutQuart'
            },
            animations: {
                radius: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                circumference: {
                    from: 0,
                    duration: 2500,
                    easing: 'easeInOutQuart'
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // BAR Kasir
    new Chart(document.getElementById('chartKasir'), {
        type: 'bar',
        data: {
            labels: [
                <?php
                    mysqli_data_seek($qKasir, 0);
                    while ($k = mysqli_fetch_assoc($qKasir)) {
                        echo "'" . $k['nama'] . "',";
                    }
                ?>
            ],
            datasets: [{
                label: 'Total Transaksi',
                data: [
                    <?php
                        mysqli_data_seek($qKasir, 0);
                        while ($k = mysqli_fetch_assoc($qKasir)) {
                            echo $k['total'] . ",";
                        }
                    ?>
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                y: {
                    duration: 1000,
                    easing: 'easeInOutQuart',
                    from: 0,
                    delay(ctx) {
                        return ctx.dataIndex * 150;
                    }
                }
            }
        }
    });

    // LINE Pendapatan
    new Chart(document.getElementById('chartPendapatan'), {
        type: 'line',
        data: {
            labels: [
                <?php mysqli_data_seek($qPendapatan, 0); while ($p = mysqli_fetch_assoc($qPendapatan)) { echo "'" . $p['tanggal'] . "',"; } ?>
            ],
            datasets: [{
                label: 'Pendapatan',
                data: [
                    <?php mysqli_data_seek($qPendapatan, 0); while ($p = mysqli_fetch_assoc($qPendapatan)) { echo $p['total'] . ","; } ?>
                ],
                borderWidth: 4,
                tension: 0.4,
                fill: false,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                x: {
                    type: 'number',
                    easing: 'linear',
                    easing: 'easeInOutQuart',
                    duration: 1000,
                    from: NaN,
                    delay(ctx) {
                        return ctx.dataIndex * 300;
                    }
                },
                y: {
                    type: 'number',
                    easing: 'linear',
                    easing: 'easeInOutQuart',
                    duration: 1000,
                    from: ctx => {
                        if (ctx.type === 'data') {
                            return 0;
                        }
                        return NaN;
                    },
                    delay(ctx) {
                        return ctx.dataIndex * 300;
                    }
                }
            }
        }
    });
    </script>
</body>

</html>