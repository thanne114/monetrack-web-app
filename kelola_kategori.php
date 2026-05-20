<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$uid = $_SESSION['user_id'];
$error = "";
$success = "";

// PROSES TAMBAH KATEGORI KUSTOM
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
    $jenis = $_POST['jenis'];

    if (!empty($nama_kategori)) {
        $insert = mysqli_query($conn, "INSERT INTO kategori (user_id, nama_kategori, jenis) VALUES ('$uid', '$nama_kategori', '$jenis')");
        if ($insert) {
            $success = "Kategori kustom berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan kategori.";
        }
    }
}

// PROSES HAPUS KATEGORI KUSTOM
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    // Proteksi: Pastikan yang dihapus adalah kategori miliknya sendiri (user_id = $uid)
    $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE id='$id_hapus' AND user_id='$uid'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "DELETE FROM kategori WHERE id='$id_hapus'");
        $success = "Kategori berhasil dihapus!";
    } else {
        $error = "Aksi ilegal! Kategori default tidak bisa dihapus.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-tags text-primary me-2"></i>Kelola Kategori Transaksi</h4>
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
    </div>

    <?php if ($error) : ?><div class="alert alert-danger py-2 small text-center"><?= $error; ?></div><?php endif; ?>
    <?php if ($success) : ?><div class="alert alert-success py-2 small text-center"><?= $success; ?></div><?php endif; ?>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3 text-primary">Buat Kategori Kustom</h6>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control form-control-sm" placeholder="Contoh: Umpan Pancing, Alat Gym" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Aliran</label>
                        <select name="jenis" class="form-select form-select-sm" required>
                            <option value="keluar">Pengeluaran (KELUAR)</option>
                            <option value="masuk">Pemasukan (MASUK)</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah_kategori" class="btn btn-primary btn-sm w-100">Simpan Kategori</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-0 overflow-hidden">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark small">
                        <tr>
                            <th>Nama Kategori</th>
                            <th class="text-center">Jenis</th>
                            <th class="text-center">Sifat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM kategori WHERE user_id IS NULL OR user_id='$uid' ORDER BY user_id ASC, jenis ASC");
                        while ($row = mysqli_fetch_assoc($query)) {
                            $badge_jenis = ($row['jenis'] == 'masuk') ? 'bg-success' : 'bg-danger';
                            $is_default = ($row['user_id'] === null);
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                <td class="text-center"><span class="badge <?= $badge_jenis; ?> text-uppercase"><?= $row['jenis']; ?></span></td>
                                <td class="text-center text-muted"><?= $is_default ? 'Bawaan' : 'Kustom'; ?></td>
                                <td class="text-center">
                                    <?php if ($is_default) : ?>
                                        <button class="btn btn-sm text-muted" disabled><i class="fas fa-lock"></i></button>
                                    <?php else : ?>
                                        <a href="kelola_kategori.php?hapus=<?= $row['id']; ?>" class="btn btn-sm text-danger" onclick="return confirm('Hapus kategori kustom ini?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>