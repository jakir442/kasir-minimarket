<?php
session_start();

include '../../../config/db.php';

$id = $_GET['id'];

// Cek apakah satuan dipakai barang
$cek = mysqli_query(
    $koneksi,
    "SELECT * FROM barang WHERE id_satuan='$id'"
);

if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error_satuan'] =
        "satuan tidak bisa dihapus karena masih digunakan barang";
    header("Location: ../../../admin/satuan/index.php");
    exit;
}

// Hapus
mysqli_query(
    $koneksi,
    "DELETE FROM satuan WHERE id_satuan='$id'"
);

$_SESSION['success_satuan'] = "satuan berhasil dihapus";

header("Location: ../../../admin/satuan/index.php");
exit;