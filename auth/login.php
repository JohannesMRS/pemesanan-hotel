<?php
// login.php - Form Login
include('../config/database.php');

// Tentukan apakah user sudah login
$logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$is_admin = $logged_in && isset($_SESSION["role"]) && $_SESSION["role"] == 'admin';

// Cek: Jika pengguna sudah login, arahkan ke dashboard yang sesuai
if ($logged_in) {
    if ($is_admin) {
        header("location: ../admin/dashboard.php");
    } else {
        header("location: home.php");
    }
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";
$success_msg = "";

// Cek parameter sukses dari register.php
if (isset($_GET['success']) && $_GET['success'] == 'register') {
    $success_msg = '<div class="alert alert-success text-center">🎉 Pendaftaran berhasil! Silakan login menggunakan akun Anda.</div>';
}


// Proses form saat data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validasi Input
    if (empty(trim($_POST["username"]))) {
        $username_err = "Mohon masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Mohon masukkan password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 2. Jika tidak ada error input, coba ambil data dari database
    if (empty($username_err) && empty($password_err)) {

        $sql = "SELECT id_user, username, password, role FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Cek jika username ada
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id_user, $username_db, $hashed_password, $role);

                    if (mysqli_stmt_fetch($stmt)) {
                        // Verifikasi Password
                        if (password_verify($password, $hashed_password)) {
                            // Password benar, buat sesi baru
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_user"] = $id_user;
                            $_SESSION["username"] = $username_db;
                            $_SESSION["role"] = $role;

                            // Arahkan sesuai role
                            if ($role == 'admin') {
                                header("location: ../admin/dashboard.php");
                            } else {
                                header("location: index.php");
                            }
                            exit;
                        } else {
                            // Password salah
                            $login_err = "Password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    // Username tidak ditemukan
                    $login_err = "Tidak ada akun yang ditemukan dengan username tersebut.";
                }
            } else {
                echo "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}

// Sertakan Header
$page_title = "Login Pengguna";
include('headerLogin.php');
?>

<div class="row justify-content-center">
    <div class="col-md-5 ">
        <h2 class="mb-4 text-center">Login ke Akun Anda</h2>

        <?php
        // Tampilkan pesan sukses dari register
        echo $success_msg;

        // Tampilkan pesan error login
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . $login_err . '</div>';
        }
        ?>

        <div class="card shadow-lg">
            <div class="card-body p-4">
                <p class="text-center text-muted">Silakan masukkan detail login Anda.</p>

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

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i> Login</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3 text-center">
            Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar sekarang</a>.
        </div>
    </div>
</div>