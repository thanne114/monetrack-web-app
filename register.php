<?php
include 'koneksi.php';

$success = false;
$error = "";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = strtolower(stripslashes(mysqli_real_escape_string($conn, $_POST['username'])));
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Cek apakah username sudah tertulis di database
    $cek_username = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    if (mysqli_num_rows($cek_username) > 0) {
        $error = "Username sudah digunakan! Silakan pilih nama lain.";
    } else {
        // Enkripsi password dengan password_hash (Standar PHP Modern & Aman, bukan MD5 lagi)
        $password_aman = password_hash($password, PASSWORD_DEFAULT);
        
        // Input ke database
        $input = mysqli_query($conn, "INSERT INTO user (username, password, nama_lengkap) VALUES ('$username', '$password_aman', '$nama')");
        if ($input) {
            $success = true;
        } else {
            $error = "Gagal mendaftar, coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reg-card { width: 100%; max-width: 420px; border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="card reg-card p-4">
    <div class="card-body">
        <h4 class="text-center fw-bold mb-3">Buat Akun Baru</h4>
        <p class="text-muted text-center small mb-4">Silakan isi data untuk menggunakan Buku Kas Digital</p>
        
        <?php if ($success) : ?>
            <div class="alert alert-success text-center small py-2">
                Pendaftaran berhasil! Silakan <a href="login.php" class="fw-bold">Login disini</a>.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger text-center small py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required autocomplete="off">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Huruf kecil tanpa spasi" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100 mb-3">Daftar Sekarang</button>
            <div class="text-center">
                <a href="login.php" class="small text-decoration-none">Sudah punya akun? Login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>