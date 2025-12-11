<?php
// admin/dashboard.php
// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/database.php');

// Fungsi cek login
if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$conn = getConnection();

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'],
    'total_hotels' => $conn->query("SELECT COUNT(*) as total FROM hotels")->fetch_assoc()['total'],
    'total_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'],
    'total_admins' => $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")->fetch_assoc()['total'],
];

// Get recent users
$recent_users = $conn->query("SELECT username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");

// Include admin header
require_once('includes/headerAdmin.php');
require_once('includes/sidebarAdmin.php');
?>

<div class="main-content" id="mainContent" style="margin-left: 250px; padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
        </h2>
        <div class="text-muted">
            <i class="fas fa-calendar me-1"></i> <?php echo date('d/m/Y'); ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Pengguna</h6>
                            <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Hotel</h6>
                            <h2 class="mb-0"><?php echo $stats['total_hotels']; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-hotel fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Pesanan</h6>
                            <h2 class="mb-0"><?php echo $stats['total_bookings']; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-list-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Admin</h6>
                            <h2 class="mb-0"><?php echo $stats['total_admins']; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Pengguna Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_users->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-warning' : 'bg-secondary'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="user/index.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Lihat Semua
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengguna terdaftar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('includes/footer.php');
$conn->close();
?>