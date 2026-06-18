<?php
session_start();

include '../../../config/db.php';

$id_barang   = $_POST['id_barang'];
$nama_barang = trim($_POST['nama_barang']);
$id_kategori = $_POST['id_kategori'];
$harga       = (int) $_POST['harga'];
$stok        = (int) $_POST['stok'];
$satuan      = $_POST['id_satuan'];

// Validasi field wajib
if (
    empty($nama_barang) ||
    empty($id_kategori) ||
    !isset($_POST['harga']) ||
    !isset($_POST['stok']) ||
    empty($satuan)
) {
    echo "
        <script>
            alert('Semua field wajib diisi!');
            window.location='../../../admin/barang/edit.php?id=$id_barang';
        </script>
    ";
    exit;
}

// Validasi harga dan stok
if ($harga < 0 || $stok < 0) {
    echo "
        <script>
            alert('Harga dan stok tidak boleh bernilai negatif!');
            window.location='../../../admin/barang/edit.php?id=$id_barang';
        </script>
    ";
    exit;
}

// Cek duplikat nama barang
$cek_barang = mysqli_query(
    $koneksi,
    "SELECT id_barang FROM barang 
    WHERE LOWER(nama_barang) = LOWER('$nama_barang')
    AND id_barang != '$id_barang'"
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