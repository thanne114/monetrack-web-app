<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        // Verifikasi password hash modern
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id']; // Simpan ID user untuk memisahkan data transaksi
            $_SESSION['nama_user'] = $row['nama_lengkap'];
            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="card-body">
        <h4 class="text-center fw-bold mb-4">Login Monetrack</h4>
        
        <?php if ($error) : ?>
            <div class="alert alert-danger text-center small py-2">Username atau password salah!</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-semibold">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordInput" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary w-100 mb-3">Masuk Aplikasi</button>
            <div class="text-center">
                <a href="register.php" class="small text-decoration-none text-success">Belum punya akun? Daftar disini</a>
            </div>
        </form>
    </div>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');
const eyeIcon = document.getElementById('eyeIcon');

togglePassword.addEventListener('click', function () {
    // Cek tipe input saat ini
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Ubah ikon jadi mata dicoret
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        // Kembalikan ikon jadi mata biasa
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});
</script>

</body>
</html>