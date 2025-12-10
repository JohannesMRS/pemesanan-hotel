<?php
// File: admin/dashboard.php
$page_title = "Dashboard";
require_once 'includes/headerAdmin.php';

function getCount($conn, $table, $where = "")
{
    $sql = "SELECT COUNT(*) AS total FROM $table $where";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_assoc()['total'];
    }
    return 0;
}

// Ambil Statistik
// Data diambil dari tabel users, bookings, dan danau_toba_tours
$total_users = getCount($conn, "users", "WHERE role = 'user'"); //
$total_bookings = getCount($conn, "bookings"); //
$pending_bookings = getCount($conn, "bookings", "WHERE status = 'pending'"); //
// Asumsi 'danau_toba_tours' adalah tabel untuk wisata
$total_tours = getCount($conn, "hotels"); // 
?>

<div class="card">
    <h2><i class="fas fa-tachometer-alt"></i> Ringkasan Dashboard</h2>
    <p>Selamat datang, **<?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>**. Berikut adalah statistik cepat sistem Anda.</p>
</div>

<div class="dashboard-grid">

    <a href="user/index.php" class="stat-card" style="background-color: #3498db;">
        <h3>Pengguna Terdaftar</h3>
        <p style="font-size: 2em; margin: 10px 0;"><?php echo $total_users; ?></p>
        <small>Akun Non-Admin</small>
    </a>

    <a href="bookings/index.php" class="stat-card" style="background-color: #9b59b6;">
        <h3>Total Pesanan</h3>
        <p style="font-size: 2em; margin: 10px 0;"><?php echo $total_bookings; ?></p>
        <small>Keseluruhan Pesanan</small>
    </a>

    <a href="bookings/index.php?status=pending" class="stat-card" style="background-color: #e67e22;">
        <h3>Pembayaran Pending</h3>
        <p style="font-size: 2em; margin: 10px 0;"><?php echo $pending_bookings; ?></p>
        <small>Perlu Konfirmasi</small>
    </a>

    <a href="wisata/index.php" class="stat-card" style="background-color: #2ecc71;">
        <h3>Destinasi Wisata</h3>
        <p style="font-size: 2em; margin: 10px 0;"><?php echo $total_tours; ?></p>
        <small>Jumlah Item Wisata</small>
    </a>
</div>

<?php
require_once 'includes/footer.php';
?>