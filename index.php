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

// Bangun kondisi WHERE untuk SQL agar selalu mengunci data milik user yang sedang login
$where_clause = " WHERE user_id = '$uid'";
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where_clause .= " AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
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
        .sidebar-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .sticky-top { top: 20px; }
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
    <div class="row">
        <div class="col-md-4">
            <div class="sticky-top">
                
                <div class="card sidebar-card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Periode</h6>
                        <form method="GET">
                            <div class="mb-2">
                                <label class="small text-muted">Dari:</label>
                                <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?php echo $tgl_awal; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted">Sampai:</label>
                                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?php echo $tgl_akhir; ?>">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Terapkan Filter</button>
                                <a href="index.php" class="btn btn-light btn-sm text-muted">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card sidebar-card mb-3 border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="text-muted small text-uppercase">Saldo Periode Ini</h6>
                        <h3 class="fw-bold mb-0 text-dark">Rp <?php echo number_format($saldo_akhir, 0, ',', '.'); ?></h3>
                    </div>
                </div>

                <div class="card sidebar-card mb-3">
                    <div class="card-body">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>

                <a href="tambah.php" class="btn btn-success w-100 shadow-sm mb-4"><i class="fas fa-plus me-2"></i>Tambah Transaksi</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Riwayat Transaksi</h6>
                    <span class="badge bg-light text-dark fw-normal">
                        <?php echo (!empty($tgl_awal)) ? $tgl_awal." s/d ".$tgl_akhir : "Semua Data"; ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Menampilkan data yang otomatis tersaring sesuai WHERE CLAUSE (Berdasarkan user_id)
                                $query_sql = "SELECT * FROM transaksi $where_clause ORDER BY tanggal DESC";
                                $query = mysqli_query($conn, $query_sql);
                                
                                if(mysqli_num_rows($query) > 0){
                                    while($row = mysqli_fetch_assoc($query)) {
                                        $warna = ($row['jenis'] == 'masuk') ? 'text-success' : 'text-danger';
                                        $icon = ($row['jenis'] == 'masuk') ? 'fa-arrow-down' : 'fa-arrow-up';
                                        echo "<tr>
                                                <td class='ps-3 small'>".date('d/m/y', strtotime($row['tanggal']))."</td>
                                                <td>".htmlspecialchars($row['keterangan'])."</td>
                                                <td class='fw-bold $warna small text-uppercase'><i class='fas $icon me-1 small'></i>{$row['jenis']}</td>
                                                <td class='fw-semibold'>Rp ".number_format($row['jumlah'], 0, ',', '.')."</td>
                                                <td class='text-center'>
                                                    <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-link text-warning'><i class='fas fa-edit'></i></a>
                                                    <a href='hapus.php?id={$row['id']}' class='btn btn-sm btn-link text-danger' onclick='return confirm(\"Hapus?\")'><i class='fas fa-trash'></i></a>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted small'>Tidak ada data transaksi ditemukan.</td></tr>";
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
            hoverOffset: 10
        }]
    },
    options: {
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20 } },
            title: { display: true, text: 'Rasio Keuangan', padding: { bottom: 10 } }
        }
    }
});
</script>
</body>
</html>