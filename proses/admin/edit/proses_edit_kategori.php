<?php
session_start();

include '../../../config/db.php';

$id_kategori   = $_POST['id_kategori'];
$nama_kategori = $_POST['nama_kategori'];

// Cek duplikat nama kategori
$cek_kategori = mysqli_query(
    $koneksi,
    "SELECT id_kategori FROM kategori 
    WHERE LOWER(nama_kategori) = LOWER('$nama_kategori')
    AND id_barang != '$id_barang'"
);

if (mysqli_num_rows($cek_kategori) > 0) {
    echo "
        <script>
            alert('Nama kategori sudah ada!');
            window.location='../../../admin/kategori/edit.php?id=$id_kategori';
        </script>
    ";
    exit;
}

mysqli_query(
    $koneksi,
    "UPDATE kategori SET
        nama_kategori='$nama_kategori'
    WHERE id_kategori='$id_kategori'"
);

$_SESSION['success_kategori'] = "Kategori berhasil diupdate";

// cek apakah ada perubahan
if (mysqli_affected_rows($koneksi) > 0) {
    $_SESSION['success_kategori'] = "Kategori berhasil diupdate";
} else {
    $_SESSION['success_kategori'] = "Tidak ada perubahan data";
}

header("Location: ../../../admin/kategori/index.php");
exit;