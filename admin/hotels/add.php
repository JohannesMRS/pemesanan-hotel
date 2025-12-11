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

// Deklarasi variabel untuk menghindari error Undefined Index saat form belum disubmit dan menyimpan nilai lama
$error = '';
$name = $description = $location = $price_per_night = $amenities = '';
$image_file_name = ''; // Nama file gambar yang akan disimpan
$is_recommended = 0;

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    $uploadOk = 1;

    // 1. Ambil dan sanitasi data input teks
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $location = $conn->real_escape_string($_POST['location']);
    $price_per_night = $conn->real_escape_string($_POST['price_per_night']);
    $amenities = $conn->real_escape_string($_POST['amenities'] ?? '');
    $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
    $created_by = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 1;

    // 2. LOGIKA UPLOAD GAMBAR
    if (!empty($_FILES["hotel_image"]["name"])) {
        $target_dir = "../../img/";

        // Buat nama file unik (untuk mencegah overwrite)
        $file_extension = strtolower(pathinfo($_FILES["hotel_image"]["name"], PATHINFO_EXTENSION));
        $image_file_name = uniqid('hotel_') . time() . '.' . $file_extension;
        $target_file = $target_dir . $image_file_name;

        // Cek tipe file (hanya gambar yang diizinkan)
        if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
        }

        // Cek ukuran file (Misalnya, maksimal 5MB)
        if ($_FILES["hotel_image"]["size"] > 5000000) {
            $error = "Maaf, ukuran file terlalu besar (Max 5MB).";
            $uploadOk = 0;
        }

        // Cek jika $uploadOk bernilai 0
        if ($uploadOk == 1) {
            if (!move_uploaded_file($_FILES["hotel_image"]["tmp_name"], $target_file)) {
                $error = "Terjadi kesalahan saat mengunggah file gambar.";
                $uploadOk = 0;
            }
        }
    }

    // 3. Masukkan data ke database HANYA jika upload berhasil atau tidak ada file yang di-upload
    if ($uploadOk == 1) {
        $sql = "INSERT INTO hotels (name, description, location, price_per_night, amenities, image_url, is_recommended, created_by) 
                VALUES (
                    '$name', 
                    '$description', 
                    '$location', 
                    '$price_per_night', 
                    '$amenities', 
                    '$image_file_name', 
                    '$is_recommended', 
                    '$created_by'
                )";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Hotel berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            header('Location: index.php');
            exit();
        } else {
            // Jika query gagal, atur error
            $error = "Error saat menambahkan data hotel: " . $conn->error;
            // Opsional: Hapus file yang sudah terlanjur terupload jika query gagal
            if (!empty($image_file_name) && file_exists($target_dir . $image_file_name)) {
                unlink($target_dir . $image_file_name);
            }
        }
    }

    $conn->close();
}

// 2. Sertakan header dan sidebar (harus setelah semua logika header() dan exit())
require_once('../includes/headerAdmin.php');
require_once('../includes/sidebarAdmin.php');
?>

<div class="main-content" id="mainContent" style="margin-left: 250px; padding: 20px;">
    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Hotel
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-custom shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Detail Hotel</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && $error !== ''): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Hotel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Hotel</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                            <small class="form-text text-muted">Informasi detail tentang hotel.</small>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_night" class="form-label">Harga per Malam (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price_per_night" name="price_per_night" min="0" step="1000" value="<?php echo htmlspecialchars($price_per_night); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="amenities" class="form-label">Fasilitas (Amenities)</label>
                            <textarea class="form-control" id="amenities" name="amenities" rows="3"><?php echo htmlspecialchars($amenities); ?></textarea>
                            <small class="form-text text-muted">Contoh: Wi-Fi, Kolam Renang, Sarapan Gratis.</small>
                        </div>

                        <div class="mb-3">
                            <label for="hotel_image" class="form-label">Gambar Utama Hotel</label>
                            <input type="file" class="form-control" id="hotel_image" name="hotel_image" accept="image/*">
                            <small class="form-text text-muted">Maksimal 5MB. Format: JPG, JPEG, PNG, GIF.</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_recommended" name="is_recommended" value="1" <?php echo ($is_recommended == 1 ? 'checked' : ''); ?>>
                                <label class="form-check-label fw-bold" for="is_recommended">
                                    <i class="fas fa-star text-warning me-1"></i> Tandai sebagai Hotel Direkomendasikan
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                            <button type="reset" class="btn btn-secondary">Reset Form</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Hotel Baru
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