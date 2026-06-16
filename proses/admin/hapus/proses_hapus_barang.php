<?php
session_start();
include '../../../config/db.php';

$id_barang = $_GET['id'];

// Hapus detail transaksi terkait
mysqli_query(
    $koneksi,
    "DELETE FROM detail_transaksi WHERE id_barang='$id_barang'"
);

// Hapus barang
mysqli_query(
    $koneksi,
    "DELETE FROM barang WHERE id_barang='$id_barang'"
);

$_SESSION['success_barang'] = "Barang berhasil dihapus";

header("Location: ../../../admin/barang/index.php");
exit;