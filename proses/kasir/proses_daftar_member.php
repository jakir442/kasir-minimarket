<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../kasir/index.php");
    exit;
}

$nama_member = trim(
    mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_member']
    )
);

$no_hp = trim(
    mysqli_real_escape_string(
        $koneksi,
        $_POST['no_hp']
    )
);

$redirect_kode = $_POST['redirect_kode'] ?? '';

// Validasi No HP harus 12 digit
if (!preg_match('/^[0-9]{12}$/', $no_hp)) {
    echo "
        <script>
            alert('Nomor HP harus terdiri dari 12 digit angka!');
            history.back();
        </script>
    ";
    exit;
}

// Cek duplikat nama atau no hp
$cek_member = mysqli_query(
    $koneksi,
    "SELECT id_member
    FROM member
    WHERE LOWER(nama_member) = LOWER('$nama_member')
    OR no_hp = '$no_hp'"
);

if (mysqli_num_rows($cek_member) > 0) {
    echo "
        <script>
            alert('Nama member atau nomor HP sudah terdaftar!');
            history.back();
        </script>
    ";
    exit;
}

// Generate kode member otomatis
$kode_member = "MB001";

$qCekUrutan = mysqli_query(
    $koneksi,
    "SELECT kode_member
    FROM member
    ORDER BY id_member DESC
    LIMIT 1"
);

if (mysqli_num_rows($qCekUrutan) > 0) {
    $dataMax = mysqli_fetch_assoc($qCekUrutan);
    $angkaUrut = (int) substr(
        $dataMax['kode_member'],
        2
    );
    $angkaUrut++;
    $kode_member =
        "MB" . sprintf("%03d", $angkaUrut);
}

// Simpan member
$query = mysqli_query(
    $koneksi,
    "INSERT INTO member (
        kode_member,
        nama_member,
        no_hp,
        status,
        tanggal_daftar
    ) VALUES (
        '$kode_member',
        '$nama_member',
        '$no_hp',
        'aktif',
        NOW()
    )"
);

if ($query) {
    header(
        "Location: ../../kasir/index.php?kode=$redirect_kode&kode_member=$kode_member"
    );
    exit;
} else {
    echo "
        <script>
            alert('Gagal mendaftarkan member!');
            history.back();
        </script>
    ";
}