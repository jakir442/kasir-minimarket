<?php
session_start();

include '../../../config/db.php';

// Ambil ID kasir
$id = $_GET['id'];

// Hapus data kasir
mysqli_query(
    $koneksi,
    "DELETE FROM pengguna
    WHERE id_pengguna='$id'
    AND role='kasir'"
);

// Session sukses
$_SESSION['success_kasir'] = "Kasir berhasil dihapus";

// Redirect
header("Location: ../../../admin/kasir/index.php");
exit;