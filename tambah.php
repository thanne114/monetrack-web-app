<?php 
include 'koneksi.php';
if(isset($_POST['submit'])){
    $tgl = $_POST['tanggal'];
    $ket = $_POST['keterangan'];
    $jns = $_POST['jenis'];
    $jml = $_POST['jumlah'];
    mysqli_query($conn, "INSERT INTO transaksi VALUES('', '$tgl', '$ket', '$jns', '$jml')");
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Tambah Transaksi</h3>
    <form method="POST" class="col-md-6">
        <input type="date" name="tanggal" class="form-control mb-2" required>
        <input type="text" name="keterangan" placeholder="Keterangan" class="form-control mb-2" required>
        <select name="jenis" class="form-control mb-2">
            <option value="masuk">Pemasukan</option>
            <option value="keluar">Pengeluaran</option>
        </select>
        <input type="number" name="jumlah" placeholder="Jumlah (Contoh: 50000)" class="form-control mb-2" required>
        <button type="submit" name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</body>
</html>