<?php
session_start();

include '../../config/db.php';

// Ambil data form
$username = $_POST['username'];
$password = $_POST['password'];

// Cari user
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pengguna WHERE username='$username'"
);

$data = mysqli_fetch_assoc($query);

// Jika user ditemukan
if ($data) {
    // Cek password
    if ($password == $data['password']) {
        // Regenerate session
        session_regenerate_id(true);
        // LOGIN ADMIN
        if ($data['role'] == 'admin') {
            $_SESSION['admin'] = [
                'id_pengguna' => $data['id_pengguna'],
                'nama'        => $data['nama'],
                'role'        => $data['role']
            ];
            header("Location: ../../admin/index.php");
            exit;
        }

        // LOGIN KASIR
        if ($data['role'] == 'kasir') {
            $_SESSION['kasir'] = [
                'id_pengguna' => $data['id_pengguna'],
                'nama'        => $data['nama'],
                'role'        => $data['role']
            ];
            header("Location: ../../kasir/index.php");
            exit;
        }
    } else {
        echo "Password salah";
    }
} else {
    echo "Username tidak ditemukan";
}
?>