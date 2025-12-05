<?php
require_once '../../config/database.php';
requireAdmin();

$conn = getConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: index.php?success=Status booking berhasil diperbarui');
    exit();
}

// Get all bookings with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$query = "
    SELECT b.*, u.username, u.email, h.name as hotel_name, h.price_per_night
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN hotels h ON b.hotel_id = h.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($status_filter) {
    $query .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_from) {
    $query .= " AND DATE(b.booking_date) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $query .= " AND DATE(b.booking_date) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$query .= " ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking - Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-calendar-check"></i> Kelola Booking</h1>
                <a href="export.php?type=csv" class="btn-export">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
            
            <!-- Filter Form -->
            <div class="card filter-card">
                <h3><i class="fas fa-filter"></i> Filter Booking</h3>
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            <a href="index.php" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Bookings Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Booking</h3>
                    <div class="booking-stats">
                        <span class="stat pending">Pending: <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'pending'; })); ?></span>
                        <span class="stat confirmed">Confirmed: <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'confirmed'; })); ?></span>
                        <span class="stat cancelled">Cancelled: <?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'cancelled'; })); ?></span>
                    </div>
                </div>