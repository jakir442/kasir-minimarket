<?php
session_start();

include '../../../config/db.php';

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "UPDATE member SET status='aktif' WHERE id_member='$id'"
);

$_SESSION['success_member'] = "Member berhasil diaktifkan";

header("Location: ../../../admin/member/index.php");
exit;