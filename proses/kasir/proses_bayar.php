<?php
session_start();

include '../../config/db.php';
include '../../config/midtrans.php';

// Ambil data form
$kode = $_POST['kode_transaksi'];
$metode = $_POST['metode_pembayaran'];
$bayar = $_POST['bayar'] ?? 0;

// TANGKAP DATA MEMBER & DISKON BARU DI SINI
$diskon = isset($_POST['diskon']) ? (int)$_POST['diskon'] : 0;
$id_member = isset($_POST['id_member']) ? mysqli_real_escape_string($koneksi, $_POST['id_member']) : '';

// Ambil transaksi
$q = mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi WHERE kode_transaksi='$kode' AND status='pending'"
);

$transaksi = mysqli_fetch_assoc($q);

if (!$transaksi) {
    echo "<script>
        alert('Transaksi tidak ditemukan atau sudah dibayar');
        window.location='../kasir/index.php';
    </script>";
    exit;
}

// Ambil detail transaksi
$qDetail = mysqli_query(
    $koneksi,
    "SELECT * FROM detail_transaksi
     WHERE kode_transaksi='$kode'"
);

// Kasir yang login
$id_pengguna = $_SESSION['kasir']['id_pengguna'] ?? 0;

if (!$id_pengguna) {
    die("Session user tidak ditemukan. Silakan login ulang.");
}

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($qDetail)) {
    $items[] = $row;
    $total += $row['subtotal'];
}

// HITUNG TOTAL AKHIR (Harga asli dikurangi diskon member)
$total_akhir = $total - $diskon;

$id_member = isset($_POST['id_member']) ? $_POST['id_member'] : '';
    $id_member_sql = ($id_member === '' || $id_member === null) ? "NULL" : (int)$id_member;

// CASH
if ($metode == 'cash') {
    // Validasi uang (Sekarang mengecek ke $total_akhir)
    if ($bayar < $total_akhir) {
        echo "<script>
            alert('Uang tidak cukup!');
            window.location='../kasir/index.php?kode=$kode';
        </script>";
        exit;
    }
    

    // Rumus kembalian yang benar
    $kembalian = $bayar - $total_akhir;

    // Update transaksi (Menyimpan $total_akhir ke database)
    // Catatan: Jika Anda sudah menambah kolom id_member & diskon di tabel transaksi, silakan tambahkan di query ini.
    mysqli_query(
        $koneksi,
        "UPDATE transaksi
        SET status='dibayar',
            metode_pembayaran='cash',
            total_harga='$total_akhir',
            diskon='$diskon',
            id_member=$id_member_sql,
            bayar='$bayar',
            kembalian='$kembalian',
            id_pengguna='$id_pengguna'
        WHERE kode_transaksi='$kode'"
    );

    // Kurangi stok
    foreach ($items as $item) {
        mysqli_query(
            $koneksi,
            "UPDATE barang
            SET stok = stok - {$item['qty']}
            WHERE id_barang = {$item['id_barang']}"
        );
    }

    $_SESSION['success'] = [
        'message' => 'Pembayaran cash berhasil!',
        'kembalian' => $kembalian,
        'time' => time()
    ];

    header("Location: ../../kasir/index.php");
    exit;
}

// ONLINE MIDTRANS
if ($metode == 'online') {
    $params = [
        'transaction_details' => [
            'order_id' => $kode,
            'gross_amount' => $total_akhir // Menggunakan total yang sudah didiskon
        ]
    ];
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    // Simpan token ke DB dengan total_harga yang sudah didiskon
    mysqli_query(
        $koneksi,
        "UPDATE transaksi
        SET metode_pembayaran='online',
            total_harga='$total_akhir',
            diskon='$diskon',
            id_member=$id_member_sql,
            snap_token='$snapToken',
            id_pengguna='$id_pengguna'
        WHERE kode_transaksi='$kode'"
    );
    $_SESSION['snap_token'] = $snapToken;
    header("Location: ../../kasir/bayar_online.php?kode=$kode");
    exit;
}