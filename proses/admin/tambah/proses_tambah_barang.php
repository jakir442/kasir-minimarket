<?php
session_start();

include '../../../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

// Ambil Data Form
$nama_barang = trim($_POST['nama_barang']);
$id_kategori = htmlspecialchars($_POST['id_kategori']);
$harga       = htmlspecialchars($_POST['harga']);
$stok        = htmlspecialchars($_POST['stok']);
$satuan      = htmlspecialchars($_POST['id_satuan']);

// Validasi
if (
    empty($nama_barang) ||
    empty($id_kategori) ||
    empty($harga) ||
    empty($stok) ||
    empty($satuan)
) {
    echo "
        <script>
            alert('Semua field wajib diisi!');
            window.location='../../../admin/barang/tambah.php';
        </script>
    ";
    exit;
}

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
            window.location='../../../admin/barang/tambah.php';
        </script>
    ";
    exit;
}

// Insert Barang
$query = mysqli_query(
    $koneksi,
    "INSERT INTO barang 
    (
        nama_barang,
        id_kategori,
        harga,
        stok,
        id_satuan
    )
    VALUES
    (
        '$nama_barang',
        '$id_kategori',
        '$harga',
        '$stok',
        '$satuan'
    )"
);

// Redirect
if ($query) {
    echo "
        <script>
            alert('Barang berhasil ditambahkan!');
            window.location='../../../admin/barang/index.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Barang gagal ditambahkan!');
            window.location='../../../admin/barang/tambah.php';
        </script>
    ";
}