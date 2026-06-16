<?php
session_start();

include '../../../config/db.php';

$id_satuan   = $_POST['id_satuan'];
$nama_satuan = $_POST['nama_satuan'];

// Cek duplikat nama satuan
$cek_satuan = mysqli_query(
    $koneksi,
    "SELECT id_satuan FROM satuan 
    WHERE LOWER(nama_satuan) = LOWER('$nama_satuan')"
);

if (mysqli_num_rows($cek_satuan) > 0) {
    echo "
        <script>
            alert('Nama satuan sudah ada!');
            window.location='../../../admin/satuan/edit.php?id=$id_satuan';
        </script>
    ";
    exit;
}

mysqli_query(
    $koneksi,
    "UPDATE satuan SET nama_satuan='$nama_satuan'
    WHERE id_satuan='$id_satuan'"
);

    // cek apakah ada perubahan
if (mysqli_affected_rows($koneksi) > 0) {
    $_SESSION['success_satuan'] = "Satuan berhasil diupdate";
} else {
    $_SESSION['success_satuan'] = "Tidak ada perubahan data";
}

header("Location: ../../../admin/satuan/index.php");
exit;