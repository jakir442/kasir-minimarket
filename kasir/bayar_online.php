<?php
session_start();

include '../config/db.php';

// ambil kode transaksi
$kode = $_GET['kode'];

// ambil transaksi
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi
     WHERE kode_transaksi='$kode'"
);

$transaksi = mysqli_fetch_assoc($query);

// ambil snap token
$snap_token = $transaksi['snap_token'];

if (!$snap_token) {

    die("Snap token tidak ditemukan");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bayar Online</title>

    <!-- MIDTRANS -->
    <script
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="SB-Mid-client-Ob4NdirpKZSup0Oe">
    </script>
</head>

<body>

    <script>
        window.snap.pay('<?= $snap_token ?>', {
            onSuccess: function(result) {
                alert("Pembayaran berhasil!");
                window.location.href = "index.php";
            },
            onPending: function(result) {
                alert("Menunggu pembayaran");
                window.location.href = "index.php";
            },
            onError: function(result) {
                alert("Pembayaran gagal!");
                window.location.href = "index.php";
            }
        });
    </script>
</body>

</html>