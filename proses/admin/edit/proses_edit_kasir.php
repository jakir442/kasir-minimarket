<?php
session_start();

include '../../../config/db.php';

// Ambil data dari form
$id_pengguna = $_POST['id_pengguna'];
$nama        = $_POST['nama'];
$username    = $_POST['username'];
$password    = $_POST['password'];

// Cek duplikat nama kasir
$cek_kasir = mysqli_query(
    $koneksi,
    "SELECT id_pengguna FROM pengguna 
    WHERE LOWER(username) = LOWER('$username')"
);

if (mysqli_num_rows($cek_kasir) > 0) {
    echo "
        <script>
            alert('Nama kasir sudah ada!');
            window.location='../../../admin/kasir/edit.php?id=$id_pengguna';
        </script>
    ";
    exit;
}

// Update data kasir
mysqli_query(
    $koneksi,
    "UPDATE pengguna SET
        nama='$nama',
        username='$username',
        password='$password'
    WHERE id_pengguna='$id_pengguna'"
);

// cek apakah ada perubahan
if (mysqli_affected_rows($koneksi) > 0) {
    $_SESSION['success_kasir'] = "Data kasir berhasil diupdate";
} else {
    $_SESSION['success_kasir'] = "Tidak ada perubahan data";
}

// Redirect
header("Location: ../../../admin/kasir/index.php");
exit;