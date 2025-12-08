<?php
// auth/register.php

// Panggil file koneksi database - PERBAIKAN DI SINI
require_once __DIR__ . '/../config/database.php';
$conn = getConnection();

$nama = $email = $password = $confirm_password = "";
$nama_err = $email_err = $password_err = $confirm_password_err = "";

// Proses form saat data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Validasi Input Dasar (Nama dan Email)
    if (empty(trim($_POST["nama"]))) {
        $nama_err = "Mohon masukkan nama.";
    } else {
        $nama = trim($_POST["nama"]);
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Mohon masukkan email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // 2. Validasi Password dan Confirm Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Mohon masukkan password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password minimal 6 karakter.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Mohon konfirmasi password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password tidak cocok.";
        }
    }

    // 3. Jika tidak ada error input, cek apakah email sudah terdaftar
    if (empty($nama_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        $sql_check = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $param_email);
            $param_email = $email;

            if (mysqli_stmt_execute($stmt_check)) {
                mysqli_stmt_store_result($stmt_check);

                if (mysqli_stmt_num_rows($stmt_check) == 1) {
                    $email_err = "Email ini sudah terdaftar.";
                } else {
                    // Email belum terdaftar, lanjutkan INSERT
                    
                    $sql_insert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
                    
                    if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
                        mysqli_stmt_bind_param($stmt_insert, "sss", $param_nama, $param_email, $param_password_hash);
                        
                        $param_nama = $nama;
                        $param_email = $email;
                        $param_password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash Password
                        
                        if (mysqli_stmt_execute($stmt_insert)) {
                            // Pendaftaran berhasil, redirect ke login dengan pesan sukses
                            header("location: login.php?success=register");
                            exit;
                        } else {
                            echo "Terjadi kesalahan sistem saat registrasi: " . mysqli_error($conn);
                        }
                        mysqli_stmt_close($stmt_insert);
                    }
                }
            }
            mysqli_stmt_close($stmt_check);
        }
    }
}

// Sertakan Header (asumsi file ini ada di auth/)
$page_title = "Daftar Akun Baru";
include('headerLogin.php');
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">Registrasi Akun</h2>
        <div class="card shadow-lg border-0">
            <div class="card-body p-4">
                <p class="text-center text-muted">Buat akun untuk memulai pemesanan.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control <?php echo (!empty($nama_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nama); ?>" required>
                        <div class="invalid-feedback"><?php echo $nama_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-person-plus-fill me-2"></i> Daftar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3 text-center">
            Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>.
        </div>
    </div>
</div>

<?php
// Hanya tutup koneksi jika $conn ada dan valid
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
// Sertakan Footer jika ada
// include('footerAuth.php');
?>