<?php
// START: Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../../config/database.php');
// Cek login
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

// 2. Sertakan header dan sidebar
require_once('../includes/headerAdmin.php');
require_once('sidebarAdmin.php');

// 3. Buat koneksi
$conn = getConnection();

// 4. Query data hotel: TAMBAHKAN KOLOM DESCRIPTION, AMENITIES, IMAGE_URL
$sql = "SELECT id, name, description, location, price_per_night, amenities, image_url, is_recommended FROM hotels ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Error: " . $conn->error);
}

$total_hotels = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hotel - Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ... CSS yang sudah ada ... */
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #2ecc71;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-custom thead {
            background: var(--primary-color);
            color: white;
        }

        .table-custom th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table-custom td {
            padding: 15px;
            vertical-align: middle;
            border-color: #eee;
        }

        .badge-recommended {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-not-recommended {
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .price-tag {
            font-weight: 600;
            color: #2ecc71;
            background: #e8f6ef;
            padding: 3px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 9999;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 45px;
            height: 45px;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: flex;
            }
        }

        /* CSS tambahan untuk tampilan gambar di tabel */
        .img-preview {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-right: 5px;
        }

        .description-truncate {
            max-width: 250px;
            /* Lebar maksimum untuk deskripsi */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <button class="mobile-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2"><i class="fas fa-hotel text-primary me-2"></i>Kelola Hotel</h1>
                    <p class="text-muted mb-0">Kelola data hotel dalam sistem</p>
                </div>
                <a href="add.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Tambah Hotel
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-custom text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Total Hotel</h6>
                                <h2 class="mb-0"><?php echo $total_hotels; ?></h2>
                            </div>
                            <i class="fas fa-hotel fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            // Hapus pesan setelah ditampilkan
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <div class="card card-custom">
            <div class="card-body">
                <?php if ($total_hotels > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Gambar</th>
                                    <th>Nama & Deskripsi</th>
                                    <th>Lokasi</th>
                                    <th>Fasilitas</th>
                                    <th width="150">Harga/Malam</th>
                                    <th width="150">Status</th>
                                    <th width="180" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                // Tentukan base URL untuk folder uploads (sesuaikan jika perlu)
                                $upload_base_url = '../../img/';
                                while ($row = $result->fetch_assoc()):
                                    $price = number_format($row['price_per_night'], 0, ',', '.');
                                    $is_recommended = $row['is_recommended'] == 1;

                                    // Tentukan sumber gambar
                                    $image_source = !empty($row['image_url']) ? $upload_base_url . htmlspecialchars($row['image_url']) : 'uploads/silimalombu.jpg';

                                    // Fungsi truncate untuk deskripsi
                                    $description_text = htmlspecialchars($row['description']);
                                    $short_description = (strlen($description_text) > 50) ? substr($description_text, 0, 50) . '...' : $description_text;

                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $counter++; ?></td>

                                        <td>
                                            <?php if (!empty($row['image_url'])): ?>
                                                <img src="<?php echo $image_source; ?>" class="img-preview" alt="Gambar Hotel">
                                            <?php else: ?>
                                                <i class="fas fa-image text-muted fa-2x"></i>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <small class="text-muted description-truncate" title="<?php echo $description_text; ?>">
                                                <?php echo $short_description ?: 'No description provided.'; ?>
                                            </small>
                                        </td>

                                        <td><?php echo htmlspecialchars($row['location']); ?></td>

                                        <td>
                                            <small class="text-info"><?php echo (strlen($row['amenities']) > 30) ? substr($row['amenities'], 0, 30) . '...' : htmlspecialchars($row['amenities'] ?: '-'); ?></small>
                                        </td>

                                        <td>
                                            <span class="price-tag">Rp <?php echo $price; ?></span>
                                        </td>

                                        <td>
                                            <?php if ($is_recommended): ?>
                                                <span class="badge-recommended">
                                                    <i class="fas fa-star me-1"></i>Direkomendasikan
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-not-recommended">
                                                    Tidak Direkomendasikan
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="edit.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-warning btn-sm"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="delete.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Hapus hotel <?php echo addslashes($row['name']); ?>?')"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan <?php echo $total_hotels; ?> hotel
                        </div>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">Belum Ada Data Hotel</h4>
                        <p class="text-muted mb-4">Tambahkan hotel pertama Anda untuk memulai</p>
                        <a href="add.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Tambah Hotel Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 text-center text-muted">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                Total data: <?php echo $total_hotels; ?> hotel |
                Terakhir diperbarui: <?php echo date('d/m/Y H:i:s'); ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const mainContent = document.getElementById('mainContent');
            const sidebar = document.querySelector('.sidebar');

            // Asumsi sidebarAdmin.php meng-*include* elemen dengan class .sidebar
            const sidebarElement = document.querySelector('.sidebar');

            if (window.getComputedStyle(sidebarElement).position === 'fixed' || window.getComputedStyle(sidebarElement).position === 'absolute') {
                // Logic untuk mobile view (sidebar fixed/absolute)
                if (sidebarElement.style.left === '0px' || sidebarElement.style.left === '') {
                    sidebarElement.style.left = '-250px';
                    mainContent.style.marginLeft = '0';
                    this.innerHTML = '<i class="fas fa-bars"></i>';
                } else {
                    sidebarElement.style.left = '0';
                    this.innerHTML = '<i class="fas fa-times"></i>';
                }
            } else {
                // Logic untuk desktop view (sidebar static/relative)
                if (mainContent.style.marginLeft === '0px' || mainContent.style.marginLeft === '') {
                    mainContent.style.marginLeft = '250px';
                    this.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    mainContent.style.marginLeft = '0';
                    this.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        });

        // Konfirmasi delete (Meskipun sudah ada inline, ini untuk kejelasan)
        function confirmDelete(hotelName) {
            return confirm(`Apakah Anda yakin ingin menghapus hotel "${hotelName}"?\n\nData yang sudah dihapus tidak dapat dikembalikan.`);
        }
    </script>

    <?php
    // Tutup koneksi
    $conn->close();

    // Include footer
    require_once('../includes/footer.php');
    ?>
</body>

</html>