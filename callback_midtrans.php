<?php

include 'config/db.php';
include 'config/midtrans.php';

// Ambil raw JSON
$json = file_get_contents("php://input");

// Kalau kosong berarti dibuka manual
if (!$json) {
    exit('Callback Midtrans');
}

// Simpan log
file_put_contents(
    'midtrans_log.txt',
    $json . PHP_EOL,
    FILE_APPEND
);

$data = json_decode($json, true);

$order_id = $data['order_id'];
$status = $data['transaction_status'];

// Ambil transaksi
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi
     WHERE kode_transaksi='$order_id'"
);

$transaksi = mysqli_fetch_assoc($query);

if (!$transaksi) {
    exit('Transaksi tidak ditemukan');
}

// SUCCESS
if (
    $status == 'settlement' ||
    $status == 'capture'
) {

    mysqli_query(
        $koneksi,
        "UPDATE transaksi
         SET status='dibayar',
             bayar=total_harga,
             kembalian='0'
         WHERE kode_transaksi='$order_id'"
    );

    // Kurangi stok
    $detail = mysqli_query(
        $koneksi,
        "SELECT * FROM detail_transaksi
         WHERE kode_transaksi='$order_id'"
    );

    while ($item = mysqli_fetch_assoc($detail)) {

        mysqli_query(
            $koneksi,
            "UPDATE barang
             SET stok = stok - {$item['qty']}
             WHERE id_barang = {$item['id_barang']}"
        );
    }
}

echo "OK";