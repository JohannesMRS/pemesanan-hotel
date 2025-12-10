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
require_once(__DIR__ . '/../config/database.php');

// Cek login
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Set page title
$page_title = "Dashboard";

// Sertakan header dan sidebar
require_once('includes/headerAdmin.php');
require_once('includes/sidebarAdmin.php');

// Buat koneksi database
$conn = getConnection();

/**
 * Fungsi untuk mendapatkan jumlah data dari tabel
 */
function getCount($conn, $table, $where = "")
{
    $sql = "SELECT COUNT(*) AS total FROM $table";
    if (!empty($where)) {
        $sql .= " $where";
    }

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['total'];
    }
    return 0;
}

// Ambil Statistik
try {
    $total_users = getCount($conn, "users", "WHERE role = 'user'");
    $total_bookings = getCount($conn, "bookings");
    $pending_bookings = getCount($conn, "bookings", "WHERE status = 'pending'");
    $total_hotels = getCount($conn, "hotels");
} catch (Exception $e) {
    // Jika ada error, set nilai default
    $total_users = 0;
    $total_bookings = 0;
    $pending_bookings = 0;
    $total_hotels = 0;
    error_log("Error fetching dashboard stats: " . $e->getMessage());
}

// Tutup koneksi setelah digunakan
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>

    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #7f8c8d;
            margin-bottom: 0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: white;
        }

        .stat-card h3 {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
            opacity: 0.9;
        }

        .stat-card p {
            font-size: 2em;
            margin: 15px 0;
            font-weight: 700;
        }

        .stat-card small {
            opacity: 0.8;
            font-size: 14px;
        }

        /* Warna untuk stat cards */
        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #e67e22, #d35400);
        }

        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .recent-activities {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .recent-activities h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #3498db;
        }

        .activity-content {
            flex: 1;
        }

        .activity-time {
            color: #95a5a6;
            font-size: 12px;
        }

        .welcome-message {
            color: #2c3e50;
            font-weight: 600;
        }

        .welcome-name {
            color: #3498db;
            font-weight: 700;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">

        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
            <p class="welcome-message">
                Selamat datang, <span class="welcome-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>.
                Berikut adalah statistik sistem Anda.
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <a href="user/index.php" class="stat-card">
                <h3><i class="fas fa-users me-2"></i>Pengguna Terdaftar</h3>
                <p><?php echo $total_users; ?></p>
                <small>Akun Non-Admin</small>
            </a>

            <a href="bookings/index.php" class="stat-card">
                <h3><i class="fas fa-list-alt me-2"></i>Total Pesanan</h3>
                <p><?php echo $total_bookings; ?></p>
                <small>Keseluruhan Pesanan</small>
            </a>

            <a href="bookings/index.php?status=pending" class="stat-card">
                <h3><i class="fas fa-clock me-2"></i>Pembayaran Pending</h3>
                <p><?php echo $pending_bookings; ?></p>
                <small>Perlu Konfirmasi</small>
            </a>

            <a href="hotels/index.php" class="stat-card">
                <h3><i class="fas fa-hotel me-2"></i>Data Hotel</h3>
                <p><?php echo $total_hotels; ?></p>
                <small>Jumlah Hotel</small>
            </a>
        </div>

        <!-- Recent Activities -->
        <div class="recent-activities">
            <h3><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h3>

            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                    <div>Pengguna baru terdaftar</div>
                    <div class="activity-time">2 jam yang lalu</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="activity-content">
                    <div>Hotel baru ditambahkan</div>
                    <div class="activity-time">Kemarin</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="activity-content">
                    <div>Pesanan dikonfirmasi</div>
                    <div class="activity-time">3 hari yang lalu</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="activity-content">
                    <div>Laporan bulanan dibuat</div>
                    <div class="activity-time">1 minggu yang lalu</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-4">
            <h4 class="mb-3">Aksi Cepat</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="hotels/add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Hotel
                </a>
                <a href="user/add.php" class="btn btn-success">
                    <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
                </a>
                <a href="bookings/index.php" class="btn btn-warning">
                    <i class="fas fa-list me-2"></i>Lihat Pesanan
                </a>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-cog me-2"></i>Pengaturan
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="mt-4 pt-4 border-top text-center text-muted">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                Sistem berjalan normal |
                Server: <?php echo date('d/m/Y H:i:s'); ?> |
                PHP: <?php echo phpversion(); ?>
            </small>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const mainContent = document.getElementById('mainContent');
            const sidebar = document.querySelector('.sidebar');

            if (mainContent.style.marginLeft === '0px' || mainContent.style.marginLeft === '') {
                mainContent.style.marginLeft = '250px';
                sidebar.style.left = '0';
                this.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                mainContent.style.marginLeft = '0';
                sidebar.style.left = '-250px';
                this.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });

        // Auto-update time
        function updateTime() {
            const timeElements = document.querySelectorAll('.system-time');
            const now = new Date();
            const timeString = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            timeElements.forEach(el => {
                el.textContent = timeString;
            });
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime(); // Initial call
    </script>

    <?php
    // Include footer
    require_once('includes/footer.php');
    ?>
</body>

</html>