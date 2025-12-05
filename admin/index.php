<?php
require_once '../config/database.php';
requireAdmin();

$conn = getConnection();

// Get statistics
$hotel_count = $conn->query("SELECT COUNT(*) as count FROM hotels")->fetch_assoc()['count'];
$user_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(total_price) as revenue FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['revenue'] ?? 0;

// Recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, u.username, h.name as hotel_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN hotels h ON b.hotel_id = h.id 
    ORDER BY b.booking_date DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent hotels
$recent_hotels = $conn->query("SELECT * FROM hotels ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Danau Toba Ticketing</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-water"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="admin-menu">
                <a href="index.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="hotels/index.php" class="menu-item">
                    <i class="fas fa-hotel"></i> Kelola Hotel
                </a>
                <a href="bookings/index.php" class="menu-item">
                    <i class="fas fa-calendar-check"></i> Kelola Booking
                </a>
                <a href="users/index.php" class="menu-item">
                    <i class="fas fa-users"></i> Kelola User
                </a>
                <a href="../index.php" class="menu-item">
                    <i class="fas fa-external-link-alt"></i> Lihat Website
                </a>
                <a href="../auth/logout.php" class="menu-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
                <div class="admin-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon hotel">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $hotel_count; ?></h3>
                        <p>Total Hotel</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon user">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $user_count; ?></h3>
                        <p>Total User</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon booking">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $booking_count; ?></h3>
                        <p>Total Booking</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card revenue">
                        <div class="stat-icon money">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rp <?php echo number_format($revenue, 0, ',', '.'); ?></h3>
                            <p>Total Pendapatan</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Booking Terbaru</h2>
                    <a href="bookings/index.php" class="btn-view-all">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Hotel</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_bookings)): ?>
                                <?php foreach($recent_bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                                    <td>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">Belum ada booking</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Hotels -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-hotel"></i> Hotel Terbaru</h2>
                    <a href="hotels/index.php" class="btn-view-all">Lihat Semua</a>
                </div>
                
                <div class="hotels-grid-small">
                    <?php if (!empty($recent_hotels)): ?>
                        <?php foreach($recent_hotels as $hotel): ?>
                        <div class="hotel-card-small">
                            <div class="hotel-image-small">
                                <img src="../assets/images/hotels/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                            </div>
                            <div class="hotel-info-small">
                                <h4><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                <p class="price-small">
                                    Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?>/malam
                                </p>
                                <div class="hotel-actions-small">
                                    <a href="hotels/edit.php?id=<?php echo $hotel['id']; ?>" 
                                       class="btn-edit-small">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hotels/delete.php?id=<?php echo $hotel['id']; ?>" 
                                       class="btn-delete-small"
                                       onclick="return confirm('Hapus hotel ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">Belum ada hotel</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>