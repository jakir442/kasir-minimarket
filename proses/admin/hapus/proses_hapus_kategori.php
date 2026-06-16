<?php
session_start();

include '../../../config/db.php';

$id = $_GET['id'];

// Cek apakah kategori dipakai barang
$cek = mysqli_query(
    $koneksi,
    "SELECT * FROM barang WHERE id_kategori='$id'"
);

if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error_kategori'] =
        "Kategori tidak bisa dihapus karena masih digunakan barang";
    header("Location: ../../../admin/kategori/index.php");
    exit;
}

// Hapus
mysqli_query(
    $koneksi,
    "DELETE FROM kategori WHERE id_kategori='$id'"
);

$_SESSION['success_kategori'] = "Kategori berhasil dihapus";

header("Location: ../../../admin/kategori/index.php");
exit;