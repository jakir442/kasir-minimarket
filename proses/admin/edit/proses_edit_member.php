<?php
session_start();

include '../../../config/db.php';

$id_member = $_POST['id_member'];
$nama_member = mysqli_real_escape_string(
    $koneksi,
    $_POST['nama_member']
);

$no_hp = mysqli_real_escape_string(
    $koneksi,
    $_POST['no_hp']
);

$status = $_POST['status'];

// cek apakah ada perubahan
$cek = mysqli_query(
    $koneksi,
    "SELECT *
    FROM member
    WHERE id_member='$id_member'"
);

$data_lama = mysqli_fetch_assoc($cek);

if (
    $data_lama['nama_member'] == $nama_member &&
    $data_lama['no_hp'] == $no_hp &&
    $data_lama['status'] == $status
) {
    header("Location: ../../../admin/member/index.php");
    exit;
}

// cek no hp
$cek_hp = mysqli_query(
    $koneksi,
    "SELECT id_member
    FROM member
    WHERE no_hp = '$no_hp'
    AND id_member != '$id_member'"
);

if (mysqli_num_rows($cek_hp) > 0) {
    die("
        <script>
            alert('Nomor HP sudah digunakan!');
            window.location='../../../admin/member/edit.php?id=$id_member';
        </script>
    ");
}

// update data
mysqli_query(
    $koneksi,
    "UPDATE member
    SET
        nama_member='$nama_member',
        no_hp='$no_hp',
        status='$status'
    WHERE id_member='$id_member'"
);

$_SESSION['success_member'] = "Data member berhasil diperbarui";

header("Location: ../../../admin/member/index.php");
exit;