<?php
include '../../config/db.php';

header('Content-Type: application/json');

$keyword = isset($_POST['q']) ? mysqli_real_escape_string($koneksi, $_POST['q']) : '';

if ($keyword == '') {
    echo json_encode([]);
    exit;
}

$query = mysqli_query(
    $koneksi, 
    "SELECT * FROM member
    WHERE 
        kode_member LIKE '%$keyword%'
        OR nama_member LIKE '%$keyword%'
        OR no_hp LIKE '%$keyword%'
        AND status = 'aktif'
    LIMIT 10"
);

$result = [];

while ($row = mysqli_fetch_assoc($query)) {
    $result[] = $row;
}

echo json_encode($result);