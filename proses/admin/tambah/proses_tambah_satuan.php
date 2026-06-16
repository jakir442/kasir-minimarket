<?php
session_start();

include '../../../config/db.php';

$nama_satuan = trim($_POST['nama_satuan']);

// Cek kosong
if (empty($nama_satuan)) {
    $_SESSION['error_satuan'] =
        "Nama satuan wajib diisi";
    header("Location: ../../../admin/satuan/tambah.php");
    exit;
}

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
            window.location='../../../admin/satuan/tambah.php';
        </script>
    ";
    exit;
}

// Simpan
mysqli_query(
    $koneksi,
    "INSERT INTO satuan(nama_satuan) VALUES('$nama_satuan')"
);

$_SESSION['success_satuan'] = "Satuan berhasil ditambahkan";

header("Location: ../../../admin/satuan/index.php");
exit;