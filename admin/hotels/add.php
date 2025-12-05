<?php
require_once '../../config/database.php';
requireAdmin();

$conn = getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $price = floatval($_POST['price']);
    $amenities = json_encode(explode(',', $_POST['amenities']));
    $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    
    // Handle image upload
    $image_url = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_url = 'hotel_' . time() . '.' . $extension;
            $upload_path = '../../assets/images/hotels/' . $image_url;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Success
            }
        }
    }
    
    // Insert hotel
    $stmt = $conn->prepare("
        INSERT INTO hotels (name, description, location, price_per_night, 
                          amenities, image_url, is_recommended, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssdssii", $name, $description, $location, $price, 
                     $amenities, $image_url, $is_recommended, $created_by);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=Hotel berhasil ditambahkan');
        exit();
    } else {
        $error = "Gagal menambahkan hotel. Silakan coba lagi.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Hotel - Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-plus"></i> Tambah Hotel Baru</h1>
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-hotel"></i> Nama Hotel *</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Masukkan nama hotel">
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Deskripsi *</label>
                        <textarea id="description" name="description" rows="4" required 
                                  placeholder="Deskripsi hotel"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="location"><i class="fas fa-map-marker-alt"></i> Lokasi *</label>
                            <input type="text" id="location" name="location" required 
                                   placeholder="Contoh: Parapat, Samosir">
                        </div>
                        
                        <div class="form-group">
                            <label for="price"><i class="fas fa-money-bill-wave"></i> Harga per Malam *</label>
                            <input type="number" id="price" name="price" required 
                                   min="0" step="1000" placeholder="Contoh: 450000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amenities"><i class="fas fa-check-circle"></i> Fasilitas (pisahkan dengan koma)</label>
                        <input type="text" id="amenities" name="amenities" 
                               placeholder="Contoh: WiFi Gratis, Kolam Renang, Restoran, Parkir Gratis">
                        <small class="form-text">Pisahkan setiap fasilitas dengan koma</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="image"><i class="fas fa-image"></i> Gambar Hotel</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_recommended" name="is_recommended" value="1">
                            <span class="checkmark"></span>
                            <i class="fas fa-star"></i> Jadikan sebagai hotel rekomendasi
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-reset">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Simpan Hotel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>