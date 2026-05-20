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
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jml = mysqli_real_escape_string($conn, $_POST['jumlah']);
    
    // LOGIKA OTOMATIS: Cari tahu 'jenis' asli (masuk/keluar) berdasarkan kategori_id yang dipilih
    $cek_jenis = mysqli_query($conn, "SELECT jenis FROM kategori WHERE id='$kategori_id'");
    $data_kategori = mysqli_fetch_assoc($cek_jenis);
    $jns = $data_kategori['jenis'];
    
    // PERBAIKAN QUERY: Menyertakan kolom kategori_id dan jenis yang sudah didapatkan secara dinamis
    $query = "INSERT INTO transaksi (user_id, kategori_id, tanggal, keterangan, jenis, jumlah) 
              VALUES ('$uid', '$kategori_id', '$tgl', '$ket', '$jns', '$jml')";
              
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
    <title>Tambah Transaksi - Monetrack</title>
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
                            <label class="form-label small fw-semibold">Kategori Transaksi</label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $uid = $_SESSION['user_id'];
                                // Mengambil data kategori default (user_id IS NULL) dan kategori buatan user sendiri
                                $get_kategori = mysqli_query($conn, "SELECT * FROM kategori WHERE user_id IS NULL OR user_id = '$uid' ORDER BY jenis ASC, nama_kategori ASC");
                                
                                while($kat = mysqli_fetch_assoc($get_kategori)) {
                                    $icon_jenis = ($kat['jenis'] == 'masuk') ? '🟢' : '🔴';
                                    $label_sifat = ($kat['user_id'] == null) ? "Bawaan" : "Kustom";
                                    
                                    echo "<option value='".$kat['id']."'>
                                            ".$icon_jenis." [".strtoupper($kat['jenis'])."] ".$kat['nama_kategori']." (".$label_sifat.")
                                          </option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Keterangan / Deskripsi</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Beli bensin, Uang saku" class="form-control" required autocomplete="off">
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