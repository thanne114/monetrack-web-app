<?php 
session_start();

// PROTEKSI: Jika belum login, tendang balik ke halaman login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if (isset($_POST['submit'])) {
    // Ambil ID user yang sedang aktif dari session
    $uid = $_SESSION['user_id'];
    
    // Ambil data dari form dan amankan dengan mysqli_real_escape_string
    $tgl = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jns = mysqli_real_escape_string($conn, $_POST['jenis']);
    $jml = mysqli_real_escape_string($conn, $_POST['jumlah']);
    
    // PERBAIKAN QUERY: Sebutkan nama kolomnya secara spesifik agar tidak error karena penambahan user_id
    $query = "INSERT INTO transaksi (user_id, tanggal, keterangan, jenis, jumlah) 
              VALUES ('$uid', '$tgl', '$ket', '$jns', '$jml')";
              
    $simpan = mysqli_query($conn, $query);
    
    if ($simpan) {
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Gagal menambah data transaksi!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi - Mataram Cash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .form-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card form-card p-4">
                <div class="card-body">
                    <h4 class="fw-bold text-primary mb-4">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Transaksi
                    </h4>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Beli bensin, Uang saku" class="form-control" required autocomplete="off">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis Transaksi</label>
                            <select name="jenis" class="form-select">
                                <option value="masuk">🟢 Pemasukan</option>
                                <option value="keluar">🔴 Pengeluaran</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" placeholder="Contoh: 50000" class="form-control" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                            <a href="index.php" class="btn btn-light text-muted px-4">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>