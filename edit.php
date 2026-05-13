<?php 
include 'koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'];

// Ambil data lama berdasarkan ID untuk ditampilkan di form
$data = mysqli_query($conn, "SELECT * FROM transaksi WHERE id=$id");
$row = mysqli_fetch_assoc($data);

// Proses Update saat tombol Simpan ditekan
if(isset($_POST['update'])){
    $tgl = $_POST['tanggal'];
    $ket = $_POST['keterangan'];
    $jns = $_POST['jenis'];
    $jml = $_POST['jumlah'];
    
    mysqli_query($conn, "UPDATE transaksi SET 
                 tanggal='$tgl', 
                 keterangan='$ket', 
                 jenis='$jns', 
                 jumlah='$jml' 
                 WHERE id=$id");
                 
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Transaksi</title>
</head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Transaksi</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?php echo $row['tanggal']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="<?php echo $row['keterangan']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-control">
                                <option value="masuk" <?php if($row['jenis'] == 'masuk') echo 'selected'; ?>>Pemasukan</option>
                                <option value="keluar" <?php if($row['jenis'] == 'keluar') echo 'selected'; ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="<?php echo $row['jumlah']; ?>" required>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>