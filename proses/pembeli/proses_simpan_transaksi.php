<?php
session_start();

include '../../config/db.php';

// Validasi keranjang
if (empty($_SESSION['keranjang'])) {
    echo "<script>
        alert('Keranjang kosong!');
        window.location='../../index.php';
    </script>";
    exit;
}

// HITUNG TOTAL
$total_harga = 0;

foreach ($_SESSION['keranjang'] as $item) {
    $total_harga += $item['harga'] * $item['qty'];
}

// Generate kode transaksi
$kode_transaksi = rand(1000, 9999);

// Simpan transaksi
$insert_transaksi = mysqli_query(
    $koneksi,
    "INSERT INTO transaksi (
        kode_transaksi,
        total_harga,
        bayar,
        kembalian,
        metode_pembayaran,
        status
    ) VALUES (
        '$kode_transaksi',
        '$total_harga',
        '0',
        '0',
        NULL,
        'pending'
    )"
);

$id_transaksi = mysqli_insert_id($koneksi);

// Simpan detail transaksi
foreach ($_SESSION['keranjang'] as $item) {
    $id_barang = $item['id_barang'];
    $qty       = $item['qty'];
    $harga     = $item['harga'];
    $subtotal  = $qty * $harga;
    
    mysqli_query(
        $koneksi,
        "INSERT INTO detail_transaksi (
            id_transaksi,
            id_barang,
            qty,
            harga,
            subtotal,
            kode_transaksi
        )
        VALUES (
            '$id_transaksi',
            '$id_barang',
            '$qty',
            '$harga',
            '$subtotal',
            '$kode_transaksi'
        )"
    );
}

$_SESSION['struk'] = [
    'kode' => $kode_transaksi,
    'tanggal' => date('d-m-Y'),
    'jam' => date('H:i'),
    'items' => $_SESSION['keranjang'],
    'total' => $total_harga
];

// Bersihkan keranjang
unset($_SESSION['keranjang']);

// Simpan kode transaksi
$_SESSION['kode_transaksi_berhasil'] = $kode_transaksi;

// Redirect
header("Location: ../../index.php?success=1");
exit;