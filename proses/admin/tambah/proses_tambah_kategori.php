<?php
session_start();

include '../../../config/db.php';

$nama_kategori = trim($_POST['nama_kategori']);

// Cek kosong
if (empty($nama_kategori)) {
    $_SESSION['error_kategori'] =
        "Nama kategori wajib diisi";
    header("Location: ../../../admin/kategori/tambah.php");
    exit;
}

// Cek duplikat nama kategori
$cek_kategori = mysqli_query(
    $koneksi,
    "SELECT id_kategori FROM kategori 
    WHERE LOWER(nama_kategori) = LOWER('$nama_kategori')"
);

if (mysqli_num_rows($cek_kategori) > 0) {
    echo "
        <script>
            alert('Nama kategori sudah ada!');
            window.location='../../../admin/kategori/tambah.php';
        </script>
    ";
    exit;
}

// Simpan
mysqli_query(
    $koneksi,
    "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')"
);

$_SESSION['success_kategori'] = "Kategori berhasil ditambahkan";

header("Location: ../../../admin/kategori/index.php");
exit;