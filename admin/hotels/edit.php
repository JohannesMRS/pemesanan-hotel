<?php
// START: Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database
require_once('../../config/database.php');

// Cek login
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$conn = getConnection();
$error = '';
$hotel_id = null;

// Pastikan ada ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID Hotel tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit();
}

$hotel_id = (int)$_GET['id'];

// --- 1. PROSES UPDATE DATA (Jika ada POST Request) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadOk = 1;
    $image_file_name = $_POST['current_image_url'] ?? ''; // Ambil nama gambar lama sebagai default

    // Ambil dan sanitasi data input teks
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $location = $conn->real_escape_string($_POST['location']);
    $price_per_night = $conn->real_escape_string($_POST['price_per_night']);
    $amenities = $_POST['amenities'] ?? '';
    $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
    $updated_at = date('Y-m-d H:i:s'); // Untuk kolom timestamp jika ada

    // LOGIKA UPLOAD GAMBAR BARU
    if (!empty($_FILES["hotel_image"]["name"])) {
        $target_dir = "../../img/";

        // Cek apakah file lama perlu dihapus (jika ada file baru)
        if (!empty($image_file_name)) {
            $old_file_path = $target_dir . $image_file_name;
            if (file_exists($old_file_path)) {
                unlink($old_file_path); // Hapus gambar lama
            }
        }

        // Buat nama file unik untuk gambar baru
        $file_extension = strtolower(pathinfo($_FILES["hotel_image"]["name"], PATHINFO_EXTENSION));
        $image_file_name = uniqid('hotel_') . time() . '.' . $file_extension;
        $target_file = $target_dir . $image_file_name;

        // Validasi file (sama seperti add.php)
        if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
        } elseif ($_FILES["hotel_image"]["size"] > 5000000) {
            $error = "Maaf, ukuran file terlalu besar (Max 5MB).";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (!move_uploaded_file($_FILES["hotel_image"]["tmp_name"], $target_file)) {
                $error = "Terjadi kesalahan saat mengunggah file gambar baru.";
                $uploadOk = 0;
            }
        }
    }

    // Eksekusi Update ke Database HANYA jika upload/validasi gambar berhasil
    if ($uploadOk == 1) {
        $sql = "UPDATE hotels SET 
                name = ?, 
                description = ?, 
                location = ?, 
                price_per_night = ?, 
                amenities = ?, 
                image_url = ?, 
                is_recommended = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsssi", $name, $description, $location, $price_per_night, $amenities, $image_file_name, $is_recommended, $hotel_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Hotel **" . htmlspecialchars($name) . "** berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            $stmt->close();
            $conn->close();
            header('Location: index.php');
            exit();
        } else {
            $error = "Error saat memperbarui data hotel: " . $conn->error;
            $stmt->close();
            // Jika query gagal, hapus gambar baru yang mungkin sudah terupload
            if (!empty($_FILES["hotel_image"]["name"]) && file_exists($target_file)) {
                unlink($target_file);
            }
        }
    }
}

// --- 2. AMBIL DATA LAMA UNTUK DITAMPILKAN DI FORM ---
$sql_fetch = "SELECT id, name, description, location, price_per_night, amenities, image_url, is_recommended FROM hotels WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $hotel_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows === 0) {
    $_SESSION['message'] = "Hotel tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    $stmt_fetch->close();
    $conn->close();
    header('Location: index.php');
    exit();
}

// Ambil data hotel yang akan diedit
$hotel_data = $result_fetch->fetch_assoc();
$stmt_fetch->close();

// Definisikan variabel untuk tampilan form
$name = $hotel_data['name'];
$description = $hotel_data['description'];
$location = $hotel_data['location'];
$price_per_night = $hotel_data['price_per_night'];
$amenities = $hotel_data['amenities'];
$image_file_name = $hotel_data['image_url'];
$is_recommended = $hotel_data['is_recommended'];

$upload_base_url = '../../img/'; // Sesuaikan dengan base url Anda

// 3. Sertakan header dan sidebar
require_once('../includes/headerAdmin.php');
require_once('../includes/sidebarAdmin.php');
?>

<div class="main-content" id="mainContent" style="margin-left: 250px; padding: 20px;">

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Hotel
        </a>
        <h3 class="mb-0 text-primary">Edit Hotel: <?php echo htmlspecialchars($name); ?></h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-custom shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Form Edit Hotel</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && $error !== ''): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="edit.php?id=<?php echo $hotel_id; ?>" enctype="multipart/form-data">

                        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($image_file_name); ?>">

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
                            <label class="form-label">Gambar Saat Ini:</label>
                            <?php if (!empty($image_file_name)): ?>
                                <div class="mb-2">
                                    <img src="<?php echo $upload_base_url . htmlspecialchars($image_file_name); ?>" alt="Gambar Hotel Saat Ini" style="max-width: 150px; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada gambar saat ini.</p>
                            <?php endif; ?>

                            <label for="hotel_image" class="form-label">Ganti Gambar Utama Hotel</label>
                            <input type="file" class="form-control" id="hotel_image" name="hotel_image" accept="image/*">
                            <small class="form-text text-muted">Unggah file baru untuk mengganti gambar saat ini. Maksimal 5MB.</small>
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
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sync-alt me-2"></i>Perbarui Hotel
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
// Tutup koneksi di akhir skrip
if (isset($conn) && $conn) {
    $conn->close();
}
?>