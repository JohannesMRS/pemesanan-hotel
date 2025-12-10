<?php
// START: Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Sertakan koneksi database
require_once('../../config/database.php');

// Cek login
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

// 2. Sertakan header dan sidebar
require_once('../includes/headerAdmin.php');
require_once('../includes/sidebarAdmin.php');

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();

    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $price_per_night = $conn->real_escape_string($_POST['price_per_night']);
    $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;

    $sql = "INSERT INTO hotels (name, location, price_per_night, is_recommended) 
            VALUES ('$name', '$location', '$price_per_night', '$is_recommended')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Hotel berhasil ditambahkan!";
        $_SESSION['message_type'] = "success";
        header('Location: index.php');
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<div class="main-content" id="mainContent" style="margin-left: 250px; padding: 20px;">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2"><i class="fas fa-plus text-primary me-2"></i>Tambah Hotel Baru</h1>
                <p class="text-muted mb-0">Isi form di bawah untuk menambahkan hotel baru</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Hotel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_night" class="form-label">Harga per Malam (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price_per_night" name="price_per_night" min="0" step="1000" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_recommended" name="is_recommended" value="1">
                                <label class="form-check-label" for="is_recommended">
                                    Tandai sebagai hotel direkomendasikan
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Hotel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('../includes/footer.php');
?>