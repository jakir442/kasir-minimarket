<?php
session_start();

include '../../../config/db.php';

// Ambil data dari form
$nama     = $_POST['nama'];
$username = $_POST['username'];
$password = $_POST['password'];

// Cek duplikat username kasir
$cek_kasir = mysqli_query(
    $koneksi,
    "SELECT id_pengguna FROM pengguna 
    WHERE LOWER(username) = LOWER('$username')"
);

if (mysqli_num_rows($cek_kasir) > 0) {
    echo "
        <script>
            alert('Username kasir sudah ada!');
            window.location='../../../admin/kasir/tambah.php';
        </script>
    ";
    exit;
}

// Simpan data kasir
mysqli_query(
    $koneksi,
    "INSERT INTO pengguna (
        nama,
        username,
        password,
        role
    ) VALUES (
        '$nama',
        '$username',
        '$password',
        'kasir'
    )"
);

// Session sukses
$_SESSION['success_kasir'] = "Kasir berhasil ditambahkan";

// Redirect
header("Location: ../../../admin/kasir/index.php");
exit;