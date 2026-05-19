<?php
session_start();

// Hapus semua data session yang melekat
$_SESSION = [];
session_unset();
session_destroy();

// Tendang kembali ke halaman login setelah berhasil logout
header("Location: login.php");
exit;
?>