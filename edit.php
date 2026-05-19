<?php 
session_start();

// 1. PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil ID transaksi dari URL dan amankan
$id = mysqli_real_escape_string($conn, $_GET['id'] ?? '');
$uid = $_SESSION['user_id'];

// 2. KEAMANAN DATA: Pastikan data transaksi yang dicari ADA dan benar-benar MILIK user yang sedang login
$query_cek = "SELECT * FROM transaksi WHERE id = '$id' AND user_id = '$uid'";
$data = mysqli_query($conn, $query_cek);

if (mysqli_num_rows($data) === 0) {
    // Jika mencoba akses ID sembarangan atau milik orang lain, tendang kembali ke dashboard
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($data);

// Proses Update saat tombol Simpan ditekan
if (isset($_POST['update'])) {
    $tgl = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jns = mysqli_real_escape_string($conn, $_POST['jenis']);
    $jml = mysqli_real_escape_string($conn, $_POST['jumlah']);
    
    // Update data dengan mengunci id transaksi dan user_id sekaligus demi keamanan ganda
    $query_update = "UPDATE transaksi SET 
                     tanggal = '$tgl', 
                     keterangan = '$ket', 
                     jenis = '$jns', 
                     jumlah = '$jml' 
                     WHERE id = '$id' AND user_id = '$uid'";
                 
    $update = mysqli_query($conn, $query_update);
    
    if ($update) {
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Gagal mengubah data transaksi!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Edit Transaksi</title>
    <style>
        body { background-color: #f0f2f5; }
        .edit-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card edit-card shadow">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Transaksi</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($row['tanggal']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="<?php echo htmlspecialchars($row['keterangan']); ?>" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis Transaksi</label>
                            <select name="jenis" class="form-select">
                                <option value="masuk" <?php if($row['jenis'] == 'masuk') echo 'selected'; ?>>🟢 Pemasukan</option>
                                <option value="keluar" <?php if($row['jenis'] == 'keluar') echo 'selected'; ?>>🔴 Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control" value="<?php echo htmlspecialchars($row['jumlah']); ?>" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update" class="btn btn-primary px-4">Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-light text-muted px-4">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>