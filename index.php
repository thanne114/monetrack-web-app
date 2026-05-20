<?php 
session_start();

// PROTEKSI: Jika belum login, tendang balik ke halaman login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php'; 

// Mengambil ID user dari session login
$uid = $_SESSION['user_id'];

// Ambil tanggal dari filter (jika ada)
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// 1. Klausul WHERE standar untuk kalkulasi total saldo (Tabel Tunggal)
$where_clause = " WHERE user_id = '$uid'";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where_clause .= " AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// 2. Klausul WHERE spesifik untuk INNER JOIN riwayat transaksi agar tidak terjadi kolisi nama kolom
$where_clause_join = " WHERE transaksi.user_id = '$uid'";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where_clause_join .= " AND transaksi.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// Ambil Data untuk Saldo & Chart berdasarkan Filter dan User Terkait
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
    <title>Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .dashboard-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: 100%; }
        .chart-container { max-height: 140px; display: flex; justify-content: center; align-items: center; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="navbar-brand mb-0 h1">
            <i class="fas fa-wallet me-2"></i>Monetrack
        </span>
        
        <div class="d-flex align-items-center gap-2">
            <span class="text-white me-2 small">
                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['nama_user']); ?>
            </span>
            <a href="edit_akun.php" class="btn btn-sm btn-light text-primary fw-semibold">
                <i class="fas fa-user-cog me-1"></i>Edit Akun
            </a>
            <a href="logout.php" class="btn btn-sm btn-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Keluar
            </a>
        </div>
    </div>
</nav>

<div class="container">
    
    <div class="row g-3 mb-4">
        
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card p-2">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="fw-bold mb-2 small"><i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Periode</h6>
                    <form method="GET">
                        <div class="row g-1 mb-2">
                            <div class="col-6">
                                <label class="text-muted" style="font-size: 11px;">Dari:</label>
                                <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?php echo $tgl_awal; ?>">
                            </div>
                            <div class="col-6">
                                <label class="text-muted" style="font-size: 11px;">Sampai:</label>
                                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?php echo $tgl_akhir; ?>">
                            </div>
                        </div>
                        <div class="row g-1">
                            <div class="col-8">
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" style="font-size: 11px;">Terapkan Filter</button>
                            </div>
                            <div class="col-4">
                                <a href="index.php" class="btn btn-light btn-sm w-100 text-muted" style="font-size: 11px;">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card border-start border-primary border-4">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="text-muted small text-uppercase mb-1">Saldo Periode Ini</h6>
                    <h3 class="fw-bold text-dark mb-1">Rp <?php echo number_format($saldo_akhir, 0, ',', '.'); ?></h3>
                    <div class="small text-muted" style="font-size: 11px;">
                        <span class="text-success fw-semibold"><i class="fas fa-arrow-down me-1"></i>Rp <?php echo number_format($total_masuk, 0, ',', '.'); ?></span>
                        <span class="mx-1">|</span>
                        <span class="text-danger fw-semibold"><i class="fas fa-arrow-up me-1"></i>Rp <?php echo number_format($total_keluar, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card">
                <div class="card-body p-2 d-flex align-items-center justify-content-center">
                    <div class="chart-container w-100">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card bg-transparent shadow-none">
                <div class="card-body p-0 d-flex flex-column gap-2 justify-content-between">
                    <a href="tambah.php" class="btn btn-success w-100 py-2 fw-semibold shadow-sm"><i class="fas fa-plus me-2"></i>Tambah Transaksi</a>
                    <a href="cetak.php?tgl_awal=<?php echo $tgl_awal; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 py-1.5 bg-white"><i class="fas fa-print me-2"></i>Cetak Laporan</a>
                    <a href="kelola_kategori.php" class="btn btn-outline-secondary btn-sm w-100 py-1.5 bg-white text-muted"><i class="fas fa-tags me-2"></i>Kelola Kategori</a>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 mb-5">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Riwayat Transaksi</h6>
                    <span class="badge bg-light text-dark fw-normal px-3 py-2 border">
                        <i class="far fa-clock me-1 text-muted"></i> <?php echo (!empty($tgl_awal)) ? date('d-m-Y', strtotime($tgl_awal))." s/d ".date('d-m-Y', strtotime($tgl_akhir)) : "Semua Data"; ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 15%;">Tanggal</th>
                                    <th style="width: 45%;">Keterangan & Kategori</th>
                                    <th style="width: 15%;">Jenis</th>
                                    <th style="width: 15%;">Jumlah</th>
                                    <th class="text-center" style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_sql = "SELECT transaksi.*, kategori.nama_kategori 
                                              FROM transaksi 
                                              INNER JOIN kategori ON transaksi.kategori_id = kategori.id 
                                              $where_clause_join 
                                              ORDER BY transaksi.tanggal DESC";
                                              
                                $query = mysqli_query($conn, $query_sql);
                                
                                if(mysqli_num_rows($query) > 0){
                                    while($row = mysqli_fetch_assoc($query)) {
                                        $warna = ($row['jenis'] == 'masuk') ? 'text-success' : 'text-danger';
                                        $icon = ($row['jenis'] == 'masuk') ? 'fa-arrow-down' : 'fa-arrow-up';
                                        ?>
                                        <tr>
                                            <td class='ps-4 small text-muted'><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                            <td>
                                                <span class="fw-semibold text-dark d-block mb-0.5"><?= htmlspecialchars($row['keterangan']); ?></span>
                                                <small class="text-muted"><i class="fas fa-tag me-1" style="font-size: 10px;"></i><?= htmlspecialchars($row['nama_kategori']); ?></small>
                                            </td>
                                            <td class='fw-bold <?= $warna; ?> small text-uppercase'><i class='fas <?= $icon; ?> me-1 small'></i><?= $row['jenis']; ?></td>
                                            <td class='fw-semibold text-dark'>Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                            <td class='text-center'>
                                                <a href='edit.php?id=<?= $row['id']; ?>' class='btn btn-sm btn-link text-warning p-1 me-1'><i class='fas fa-edit'></i></a>
                                                <a href='hapus.php?id=<?= $row['id']; ?>' class='btn btn-sm btn-link text-danger p-1' onclick='return confirm("Hapus transaksi ini?")'><i class='fas fa-trash'></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted small'><i class='fas fa-folder-open d-block fs-2 mb-2 text-black-50'></i>Tidak ada data transaksi ditemukan pada periode ini.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('myChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Masuk', 'Keluar'],
        datasets: [{
            data: [<?php echo $total_masuk; ?>, <?php echo $total_keluar; ?>],
            backgroundColor: ['#198754', '#dc3545'],
            borderWidth: 2,
            hoverOffset: 5
        }]
    },
    options: {
        cutout: '75%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'right', 
                labels: { boxWidth: 10, font: { size: 11 }, padding: 10 } 
            },
            title: { display: false }
        }
    }
});
</script>
</body>
</html>