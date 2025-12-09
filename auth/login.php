<?php
// auth/login.php
session_start();
// Panggil file koneksi database
include('../config/database.php'); 
$conn = getConnection(); // 
// Tentukan apakah user sudah login
$logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$is_admin = $logged_in && isset($_SESSION["role"]) && $_SESSION["role"] == 'admin';

// Cek: Jika pengguna sudah login, arahkan ke dashboard yang sesuai
if ($logged_in) {
    if ($is_admin) {
        header("location: ../admin/dashboard.php");
    } else {
        header("location: ../pages/home.php"); // Arahkan ke root folder jika user biasa
    }
    exit;
}

$login_identity = $password = $role_choice = "";
$identity_err = $password_err = $role_choice_err = $login_err = "";
$success_msg = "";

// Cek parameter sukses dari register.php
if (isset($_GET['success']) && $_GET['success'] == 'register') {
    $success_msg = '<div class="alert alert-success text-center">🎉 Pendaftaran berhasil! Silakan login menggunakan akun Anda.</div>';
}

// Proses form saat data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validasi Input
    if (empty(trim($_POST["identity"]))) {
        $identity_err = "Mohon masukkan Username atau Email.";
    } else {
        $login_identity = trim($_POST["identity"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Mohon masukkan password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty(trim($_POST["role_choice"]))) {
        $role_choice_err = "Pilih peran Anda.";
    } else {
        $role_choice = trim($_POST["role_choice"]);
    }

    // 2. Jika tidak ada error input, coba ambil data dari database
    if (empty($identity_err) && empty($password_err) && empty($role_choice_err)) {

        // Cek apakah input adalah email atau username, lalu gunakan query yang sesuai
        if (filter_var($login_identity, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT id, email AS identity, password, role FROM users WHERE email = ? AND role = ?";
        } else {
            $sql = "SELECT id, username AS identity, password, role FROM users WHERE username = ? AND role = ?";
        }

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_identity, $param_role);
            $param_identity = $login_identity;
            $param_role = $role_choice;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Cek jika akun ditemukan dengan role yang dipilih
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id_user, $username_or_email_db, $hashed_password, $role);

                    if (mysqli_stmt_fetch($stmt)) {
                        // Verifikasi Password
                        if (password_verify($password, $hashed_password)) {
                            // Password benar, buat sesi baru
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id_user;
                            $_SESSION["username"] = $username_or_email_db; // Simpan identitas yang digunakan untuk login
                            $_SESSION["role"] = $role;

                            // Arahkan sesuai role
                            if ($role == 'admin') {
                                header("location: ../admin/dashboard.php");
                            } else {
                                header("location: ../pages/home.php");
                            }
                            exit;
                        } else {
                            // Password salah
                            $login_err = "Password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    // Akun tidak ditemukan dengan kombinasi identitas dan peran
                    $login_err = "Kombinasi akun atau peran yang Anda pilih tidak valid.";
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
        echo $success_msg;
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . $login_err . '</div>';
        }
        ?>

        <div class="card shadow-lg border-0">
            <div class="card-body p-4">
                <p class="text-center text-muted">Silakan masukkan detail login Anda.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <div class="mb-3">
                        <label for="identity" class="form-label">Username atau Email</label>
                        <input type="text" name="identity" class="form-control <?php echo (!empty($identity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($login_identity); ?>" required>
                        <div class="invalid-feedback"><?php echo $identity_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role_choice" class="form-label">Masuk Sebagai</label>
                        <select name="role_choice" class="form-select <?php echo (!empty($role_choice_err)) ? 'is-invalid' : ''; ?>" required>
                            <option value="" disabled selected>Pilih salah satu peran</option>
                            <option value="user" <?php echo ($role_choice == 'user' ? 'selected' : ''); ?>>User Biasa</option>
                            <option value="admin" <?php echo ($role_choice == 'admin' ? 'selected' : ''); ?>>Administrator</option>
                        </select>
                        <div class="invalid-feedback"><?php echo $role_choice_err; ?></div>
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

<?php
mysqli_close($conn);
// Sertakan Footer jika ada
// include('footerAuth.php');
?>