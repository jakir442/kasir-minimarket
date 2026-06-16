<?php
session_start();

include '../../../config/db.php';

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "UPDATE member SET status='nonaktif' WHERE id_member='$id'"
);

$_SESSION['success_member'] = "Member berhasil dinonaktifkan";

header("Location: ../../../admin/member/index.php");
exit;