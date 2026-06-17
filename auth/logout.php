<?php
session_start();

// Hapus session admin jika ada
unset($_SESSION['admin']);

// Hapus session kasir jika ada
unset($_SESSION['kasir']);

header("Location: ./login.php");