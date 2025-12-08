<<<<<<< HEAD
<?php require_once '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Danau Toba Ticketing</title>
    <link rel="stylesheet" href="../assets/style/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2><i class="fas fa-user-plus"></i> Daftar Akun Baru</h2>
                <p>Bergabung dengan kami untuk memesan hotel di Danau Toba</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form action="proses_register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Masukkan username Anda">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Masukkan email Anda">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Minimal 8 karakter">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Ulangi password Anda">
                </div>
                
                <button type="submit" class="btn-auth">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
                <p><a href="../pages/home.php"><i class="fas fa-home"></i> Kembali ke Home</a></p>
=======
<?php
// register.php - Form Pendaftaran Pengguna
include('../config/database.php');

// Cek: Jika pengguna sudah login, arahkan ke halaman utama
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = $general_err = "";

// Proses form saat data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validasi Username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Mohon masukkan username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    } else {
        // Cek apakah username sudah terdaftar di database
        $sql = "SELECT id FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "Username ini sudah digunakan.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $general_err = "Terjadi kesalahan database saat cek username.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // 2. Validasi Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Mohon masukkan password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password harus memiliki minimal 6 karakter.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 3. Validasi Konfirmasi Password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Mohon konfirmasi password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Konfirmasi password tidak cocok.";
        }
    }

    // 4. Jika tidak ada error, masukkan user baru ke database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($general_err)) {

        // Perhatian: Hanya memasukkan username, password, dan role (DEFAULT 'user')
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);

            $param_username = $username;
            // Gunakan password_hash() untuk keamanan
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if (mysqli_stmt_execute($stmt)) {
                // Pendaftaran berhasil, arahkan ke halaman login dengan pesan sukses
                header("location: login.php?success=register");
                exit;
            } else {
                $general_err = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}

// Sertakan Header
$page_title = "Daftar Akun Baru";
include('headerLogin.php');
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">Daftar Akun Baru</h2>

        <?php
        if (!empty($general_err)) {
            echo '<div class="alert alert-danger"><i class="bi bi-x-octagon-fill me-2"></i>' . $general_err . '</div>';
        }
        ?>

        <div class="card shadow-lg">
            <div class="card-body p-4">
                <p class="text-center text-muted">Silakan isi formulir di bawah untuk membuat akun baru.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-person-plus-fill me-2"></i> Daftar</button>
                    </div>
                </form>
>>>>>>> ef028e9cbf4ac7af04be5ebf0cf97dc196f3de5c
            </div>
        </div>

        <div class="mt-3 text-center">
            Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>.
        </div>
    </div>
</div>

