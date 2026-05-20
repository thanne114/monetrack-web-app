<?php
session_start();

// 1. PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$uid = $_SESSION['user_id'];
$error = "";
$success = "";

// 2. AMBIL DATA AKUN SAAT INI
$query_user = mysqli_query($conn, "SELECT * FROM user WHERE id = '$uid'");
$user = mysqli_fetch_assoc($query_user);

// 3. PROSES UPDATE SAAT TOMBOL DIKLIK
if (isset($_POST['update_akun'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = strtolower(stripslashes(mysqli_real_escape_string($conn, $_POST['username'])));
    $password_baru = $_POST['password'];

    // Cek apakah username baru sudah dipakai oleh ORANG LAIN
    $cek_username = mysqli_query($conn, "SELECT id FROM user WHERE username = '$username' AND id != '$uid'");
    
    if (mysqli_num_rows($cek_username) > 0) {
        $error = "Username sudah digunakan oleh orang lain! Silakan pilih nama lain.";
    } else {
        // Jika password diisi, artinya user ingin mengganti password
        if (!empty($password_baru)) {
            $password_aman = password_hash($password_baru, PASSWORD_DEFAULT);
            $query_update = "UPDATE user SET username = '$username', password = '$password_aman', nama_lengkap = '$nama' WHERE id = '$uid'";
        } else {
            // Jika password kosong, hanya update nama dan username saja
            $query_update = "UPDATE user SET username = '$username', nama_lengkap = '$nama' WHERE id = '$uid'";
        }

        $update = mysqli_query($conn, $query_update);

        if ($update) {
            // Perbarui data session aktif agar nama di navbar langsung berubah
            $_SESSION['nama_user'] = $nama;
            $success = "Akun berhasil diperbarui!";
            
            // Refresh data user yang tampil di form
            $query_user = mysqli_query($conn, "SELECT * FROM user WHERE id = '$uid'");
            $user = mysqli_fetch_assoc($query_user);
        } else {
            $error = "Gagal memperbarui akun, coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Akun - Monetrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .profile-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card profile-card p-4">
                <div class="card-body">
                    <h4 class="fw-bold text-primary mb-4">
                        <i class="fas fa-user-edit me-2"></i>Pengaturan Akun
                    </h4>

                    <?php if ($success) : ?>
                        <div class="alert alert-success small py-2"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error) : ?>
                        <div class="alert alert-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required autocomplete="off">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required autocomplete="off">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small">Tinggalkan kolom ini kosong jika hanya ingin mengubah nama atau username.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update_akun" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="index.php" class="btn btn-light text-muted px-4">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');
const eyeIcon = document.getElementById('eyeIcon');

togglePassword.addEventListener('click', function () {
    // Cek tipe input saat ini, lalu balikkan kondisinya
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Ganti icon menjadi mata dicoret (fa-eye-slash)
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        // Ganti balik menjadi ikon mata biasa (fa-eye)
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});
</script>

</body>
</html>