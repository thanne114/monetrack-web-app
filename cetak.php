<?php 
session_start();

// PROTEKSI: Jika belum login, tendang balik
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php'; 

$uid = $_SESSION['user_id'];

// Ambil parameter filter tanggal dari URL (jika ada)
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// Bangun kondisi WHERE yang sama persis dengan index.php
$where_clause = " WHERE user_id = '$uid'";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where_clause .= " AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// Ambil data untuk ringkasan laporan
$m = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi $where_clause AND jenis='masuk'");
$total_masuk = mysqli_fetch_assoc($m)['total'] ?? 0;

$k = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi $where_clause AND jenis='keluar'");
$total_keluar = mysqli_fetch_assoc($k)['total'] ?? 0;

$saldo_akhir = $total_masuk - $total_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Keuangan - Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #fff; color: #000; font-size: 14px; }
        .line-dan-nama { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Trik CSS: Menyembunyikan tombol cetak saat kertas/PDF dicetak */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="text-center line-dan-nama">
        <h3 class="fw-bold text-uppercase mb-1">Laporan Arus Kas Keuangan</h3>
        <h5 class="fw-normal mb-1">Aplikasi Monetrack</h5>
        <p class="text-muted small mb-0">Nama Pengguna: <strong><?php echo htmlspecialchars($_SESSION['nama_user']); ?></strong></p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span>Periode Laporan: </span>
            <span class="badge bg-secondary text-white fw-bold">
                <?php echo (!empty($tgl_awal) && !empty($tgl_akhir)) ? date('d-m-Y', strtotime($tgl_awal))." s/d ".date('d-m-Y', strtotime($tgl_akhir)) : "Semua Data"; ?>
            </span>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary btn-sm me-1">
                <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card p-2 border-dark">
                <small class="text-muted text-uppercase d-block">Total Pemasukan</small>
                <span class="fw-bold text-success">Rp <?php echo number_format($total_masuk, 0, ',', '.'); ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="card p-2 border-dark">
                <small class="text-muted text-uppercase d-block">Total Pengeluaran</small>
                <span class="fw-bold text-danger">Rp <?php echo number_format($total_keluar, 0, ',', '.'); ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="card p-2 border-dark bg-light">
                <small class="text-muted text-uppercase d-block mb-0">Saldo Akhir</small>
                <span class="fw-bold text-dark">Rp <?php echo number_format($saldo_akhir, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th width="15%">Tanggal</th>
                <th>Keterangan / Deskripsi Transaksi</th>
                <th width="15%">Jenis</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // PERBAIKAN QUERY: Ditambahkan INNER JOIN agar bisa memanggil kategori.nama_kategori
            $query_sql = "SELECT transaksi.*, kategori.nama_kategori 
                          FROM transaksi 
                          INNER JOIN kategori ON transaksi.kategori_id = kategori.id 
                          $where_clause 
                          ORDER BY transaksi.tanggal DESC";
                          
            $query = mysqli_query($conn, $query_sql);
            
            if(mysqli_num_rows($query) > 0){
                while($row = mysqli_fetch_assoc($query)) {
                    $warna = ($row['jenis'] == 'masuk') ? 'text-success' : 'text-danger';
                    ?>
                    <tr>
                        <td class='text-center'><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td>
                            <span class="fw-semibold"><?= htmlspecialchars($row['keterangan']); ?></span>
                            <br><small class="text-muted"><em>Kategori: <?= htmlspecialchars($row['nama_kategori']); ?></em></small>
                        </td>
                        <td class='text-center fw-bold text-uppercase <?= $warna; ?>'><?= $row['jenis']; ?></td>
                        <td class='text-end fw-semibold'>Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Tidak ada histori data pada periode ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <div class="row mt-5 pt-3">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p class="mb-5">Mataram, <?= date('d M Y'); ?></p>
            <p class="fw-bold text-decoration-underline mb-0"><?= htmlspecialchars($_SESSION['nama_user']); ?></p>
            <small class="text-muted">User Monetrack</small>
        </div>
    </div>
</div>

<script>
    // Otomatis membuka jendela cetak saat halaman selesai dimuat
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>