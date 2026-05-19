<?php 
session_start();

// 1. PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil ID dari URL dan amankan dengan mysqli_real_escape_string
$id = mysqli_real_escape_string($conn, $_GET['id'] ?? '');
$uid = $_SESSION['user_id'];

// 2. EKSEKUSI AMAN: Hanya hapus jika ID transaksi DAN user_id-nya cocok dengan yang login
$query_hapus = "DELETE FROM transaksi WHERE id = '$id' AND user_id = '$uid'";
$hapus = mysqli_query($conn, $query_hapus);

// Periksa apakah ada baris di database yang terpengaruh/terhapus
if (mysqli_affected_rows($conn) > 0) {
    // Jika berhasil dihapus, langsung kembali ke index.php
    header("Location: index.php");
    exit;
} else {
    // Jika gagal (misal karena mencoba hapus ID random milik orang lain), tetap lempar balik ke index.php
    header("Location: index.php");
    exit;
}
?>