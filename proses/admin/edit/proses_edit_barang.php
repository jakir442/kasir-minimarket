<?php
session_start();

include '../../../config/db.php';

$id_barang   = $_POST['id_barang'];
$nama_barang = $_POST['nama_barang'];
$id_kategori = $_POST['id_kategori'];
$harga       = $_POST['harga'];
$stok        = $_POST['stok'];
$satuan      = $_POST['id_satuan'];

// Cek duplikat nama barang
$cek_barang = mysqli_query(
    $koneksi,
    "SELECT id_barang FROM barang 
    WHERE LOWER(nama_barang) = LOWER('$nama_barang')"
);

if (mysqli_num_rows($cek_barang) > 0) {
    echo "
        <script>
            alert('Nama barang sudah ada!');
            window.location='../../../admin/barang/edit.php?id=$id_barang';
        </script>
    ";
    exit;
}

mysqli_query(
    $koneksi,
    "UPDATE barang SET
        nama_barang='$nama_barang',
        id_kategori='$id_kategori',
        harga='$harga',
        stok='$stok',
        id_satuan='$satuan'
    WHERE id_barang='$id_barang'"
);

// cek apakah ada perubahan
if (mysqli_affected_rows($koneksi) > 0) {
    $_SESSION['success_barang'] = "Barang berhasil diupdate";
} else {
    $_SESSION['success_barang'] = "Tidak ada perubahan data";
}

header("Location: ../../../admin/barang/index.php");
exit;
?>