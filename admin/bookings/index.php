<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
include '../includes/headerAdmin.php';
require_once 'sidebarAdmin.php';    
requireLogin();
requireAdmin();

$page_title = "Kelola Pesanan";

// Koneksi database
$conn = getConnection();

// Query untuk mendapatkan data bookings - DIPERBAIKI DENGAN COALESCE
$sql = "SELECT 
            b.id_booking,
            b.id,
            COALESCE(u.username, 'Guest / Tidak tercatat') as username,
            COALESCE(u.email, 'N/A') as email,
            h.name as hotel_name,
            b.check_in,
            b.check_out,
            b.guests,
            b.total_price,
            b.status,
            b.booking_date
        FROM bookings b
        LEFT JOIN users u ON b.id = u.id
        LEFT JOIN hotels h ON b.hotel_id = h.id
        ORDER BY b.booking_date DESC";

$result = $conn->query($sql);

// Hitung statistik
$stats_sql = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM bookings";

$stats_result = $conn->query($stats_sql);
if (!$stats_result) {
    $stats = [
        'total_bookings' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'cancelled' => 0
    ];
} else {
    $stats = $stats_result->fetch_assoc();
}
?>

<div class="admin-container">
    <!-- Main Content -->
    <main class="admin-content">
        <div class="content-header">
            <h1><i class="fas fa-clipboard-list"></i> Kelola Pesanan</h1>
            <p>Ini adalah halaman untuk melihat dan mengelola semua pesanan hotel.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Pesanan</h3>
                    <p class="stat-number"><?php echo $stats['total_bookings']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Pending</h3>
                    <p class="stat-number"><?php echo $stats['pending']; ?></p>
                </div>
            </div>
            
        </div>

        <!-- Filter Section -->
        <!-- <div class="filter-section">
            <div class="filter-group">
                <label for="filter-status"><i class="fas fa-filter"></i> Status Pesanan:</label>
                <select id="filter-status" class="filter-select">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Dikonfirmasi</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="search-pesanan"><i class="fas fa-search"></i> Cari:</label>
                <input type="text" id="search-pesanan" placeholder="Cari nama/hotel..." class="search-input">
            </div>
            <button class="btn-refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div> -->

        <!-- Orders Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> Daftar Pesanan</h3>
                <span class="table-count">Total: <?php echo $result->num_rows; ?> pesanan</span>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Tamu</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal Pesan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-status="<?php echo $row['status']; ?>">
                                <td>#<?php echo str_pad($row['id_booking'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="user-info">
                                        <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                        <small><?php echo htmlspecialchars($row['email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['hotel_name'] ?? 'Hotel tidak ditemukan'); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['check_out'])); ?></td>
                                <td><?php echo $row['guests']; ?> orang</td>
                                <td class="price">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php
                                        $status_text = [
                                            'pending' => 'Pending',
                                            'confirmed' => 'Dikonfirmasi',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_text[$row['status']] ?? ucfirst($row['status']);
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($row['booking_date'])); ?></td>
                                <td class="action-buttons">
                                    <button class="btn-view" onclick="viewBooking(<?php echo $row['id_booking']; ?>)" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-edit" onclick="editBooking(<?php echo $row['id_booking']; ?>)" title="Edit Pesanan">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-delete" onclick="deleteBooking(<?php echo $row['id_booking']; ?>)" title="Hapus Pesanan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list fa-3x"></i>
                                    <p>Belum ada pesanan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn prev" disabled><i class="fas fa-chevron-left"></i> Prev</button>
            <span class="page-number active">1</span>
            <button class="page-btn next">Next <i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Footer -->
        <footer class="admin-footer">
            <p>2025 Danau Toba Ticketing System - Admin Panel v1.0</p>
            <p id="current-time"></p>
        </footer>
    </main>
</div>



<!-- Modal Detail Pesanan -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2><i class="fas fa-info-circle"></i> Detail Pesanan</h2>
        <div id="modal-body">
            <!-- Detail akan diisi via JavaScript -->
        </div>
        <div class="modal-actions">
            <button class="btn-close-modal">Tutup</button>
        </div>
    </div>
</div>

<script>
    // Filter Table
    document.getElementById('filter-status').addEventListener('change', filterTable);
    document.getElementById('search-pesanan').addEventListener('input', filterTable);

    function filterTable() {
        const statusFilter = document.getElementById('filter-status').value;
        const searchQuery = document.getElementById('search-pesanan').value.toLowerCase();
        const rows = document.querySelectorAll('.admin-table tbody tr');

        let visibleCount = 0;

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const rowText = row.textContent.toLowerCase();

            const statusMatch = statusFilter === 'all' || status === statusFilter;
            const searchMatch = searchQuery === '' || rowText.includes(searchQuery);

            if (statusMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update count
        document.querySelector('.table-count').textContent = `Total: ${visibleCount} pesanan`;
    }

    // View Booking Detail
    function viewBooking(id) {
        // Buat modal content dengan data dari row
        const btn = event.target.closest('.btn-view');
        const row = btn.closest('tr');

        const modalBody = document.getElementById('modal-body');
        const bookingId = row.cells[0].textContent.replace('#', '');
        const username = row.cells[1].querySelector('strong').textContent;
        const email = row.cells[1].querySelector('small').textContent;
        const hotel = row.cells[2].textContent;
        const checkin = row.cells[3].textContent;
        const checkout = row.cells[4].textContent;
        const guests = row.cells[5].textContent;
        const total = row.cells[6].textContent;
        const status = row.cells[7].querySelector('.status-badge').textContent;
        const bookingDate = row.cells[8].textContent;

        modalBody.innerHTML = `
            <div class="booking-detail">
                <div class="detail-section">
                    <h3><i class="fas fa-id-card"></i> Informasi Pesanan</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>ID Pesanan:</label>
                            <span>#${bookingId}</span>
                        </div>
                        <div class="detail-item">
                            <label>Tanggal Pesan:</label>
                            <span>${bookingDate}</span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-badge status-${status.toLowerCase()}">${status}</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3><i class="fas fa-user"></i> Informasi Pengguna</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Nama:</label>
                            <span>${username}</span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span>${email}</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3><i class="fas fa-hotel"></i> Detail Hotel</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Hotel:</label>
                            <span>${hotel}</span>
                        </div>
                        <div class="detail-item">
                            <label>Check-in:</label>
                            <span>${checkin}</span>
                        </div>
                        <div class="detail-item">
                            <label>Check-out:</label>
                            <span>${checkout}</span>
                        </div>
                        <div class="detail-item">
                            <label>Jumlah Tamu:</label>
                            <span>${guests}</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3><i class="fas fa-money-bill-wave"></i> Pembayaran</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Total Harga:</label>
                            <span class="price">${total}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('detailModal').style.display = 'block';
    }

    // Edit Booking
    function editBooking(id) {
        if (confirm(`Edit pesanan #${id.toString().padStart(4, '0')}?`)) {
            // Redirect ke halaman edit
            window.location.href = `edit.php?id=${id}`;
        }
    }

    // Delete Booking
    // Delete Booking - VERSION UPDATE
    function deleteBooking(id) {
        const bookingId = id.toString().padStart(4, '0');

        // Konfirmasi dengan SweetAlert (opsional) atau alert biasa
        if (confirm(`HAPUS PESANAN #${bookingId}?\n\nApakah Anda yakin ingin menghapus pesanan ini?\nTindakan ini tidak dapat dibatalkan!`)) {

            // Tampilkan loading di tombol
            const deleteBtn = event.target.closest('.btn-delete');
            const originalHTML = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            deleteBtn.disabled = true;
            deleteBtn.style.opacity = '0.7';

            // Kirim data dengan FormData
            const formData = new FormData();
            formData.append('id', id);

            // Kirim request ke delete.php
            fetch('delete.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset tombol
                    deleteBtn.innerHTML = originalHTML;
                    deleteBtn.disabled = false;
                    deleteBtn.style.opacity = '1';

                    if (data.success) {
                        // Tampilkan pesan sukses
                        showNotification('success', `Pesanan #${bookingId} berhasil dihapus!`);

                        // Hapus baris dari tabel
                        const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
                        if (row) {
                            // Animasi fade out
                            row.style.backgroundColor = '#ffe6e6';
                            row.style.transition = 'all 0.5s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(100px)';

                            // Hapus setelah animasi
                            setTimeout(() => {
                                row.remove();
                                // Update counter
                                updateTableCount();
                                // Update stats
                                updateStatsCard();
                            }, 500);
                        } else {
                            // Jika tidak ada data-booking-id, reload halaman
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        showNotification('error', 'Gagal menghapus: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Terjadi kesalahan saat menghapus pesanan.');
                    // Reset tombol
                    deleteBtn.innerHTML = originalHTML;
                    deleteBtn.disabled = false;
                    deleteBtn.style.opacity = '1';
                });
        }
    }

    // Fungsi notifikasi baru
    function showNotification(type, message) {
        // Hapus notifikasi sebelumnya
        const existing = document.querySelector('.custom-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;
        notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
        <button class="close-notification">&times;</button>
    `;

        document.body.appendChild(notification);

        // Tampilkan dengan animasi slide
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Auto hide setelah 5 detik
        const autoHide = setTimeout(() => {
            hideNotification(notification);
        }, 5000);

        // Tombol close
        notification.querySelector('.close-notification').addEventListener('click', () => {
            clearTimeout(autoHide);
            hideNotification(notification);
        });
    }

    function hideNotification(notification) {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    // Update counter tabel
    function updateTableCount() {
        const rows = document.querySelectorAll('.admin-table tbody tr');
        const visibleRows = Array.from(rows).filter(row =>
            row.style.display !== 'none' &&
            !row.classList.contains('deleted')
        ).length;

        document.querySelector('.table-count').textContent = `Total: ${visibleRows} pesanan`;
    }

    // Update stats card (hitung ulang)
    function updateStatsCard() {
        // Hitung ulang stats dari tabel yang terlihat
        const rows = document.querySelectorAll('.admin-table tbody tr');
        let pending = 0,
            confirmed = 0,
            cancelled = 0,
            total = 0;

        rows.forEach(row => {
            if (row.style.display !== 'none' && !row.classList.contains('deleted')) {
                const status = row.getAttribute('data-status');
                total++;

                if (status === 'pending') pending++;
                else if (status === 'confirmed') confirmed++;
                else if (status === 'cancelled') cancelled++;
            }
        });

        // Update angka di stat cards
        document.querySelector('.stat-number:nth-child(1)').textContent = total;
        document.querySelector('.stat-number:nth-child(2)').textContent = pending;
        document.querySelector('.stat-number:nth-child(3)').textContent = confirmed;
        document.querySelector('.stat-number:nth-child(4)').textContent = cancelled;
    }



    // Tambahkan di DOMContentLoaded untuk set data-booking-id
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.admin-table tbody tr');
        rows.forEach((row) => {
            const bookingId = row.cells[0].textContent.replace('#', '');
            row.setAttribute('data-booking-id', bookingId);
        });
    });

    // Modal functionality
    const modal = document.getElementById('detailModal');
    const closeBtn = document.querySelector('.close-modal');
    const closeBtn2 = document.querySelector('.btn-close-modal');

    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    closeBtn2.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Update waktu
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('current-time').textContent = timeString;
    }

    setInterval(updateTime, 1000);
    updateTime();

    // Tambahkan data-booking-id ke setiap row
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.admin-table tbody tr');
        rows.forEach((row, index) => {
            const bookingId = row.cells[0].textContent.replace('#', '');
            row.setAttribute('data-booking-id', bookingId);
        });
    });
</script>

<style>
    /* Admin Styles */
    .admin-container {
        display: flex;
        min-height: 100vh;
        background: #f8f9fa;
    }

    .admin-content {
        flex: 1;
        margin-left: 250px;
        padding: 25px;
    }

    .content-header {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        border-left: 5px solid #1e3c72;
    }

    .content-header h1 {
        margin: 0;
        color: #1e3c72;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.8rem;
    }

    .content-header p {
        margin: 10px 0 0;
        color: #6c757d;
        font-size: 1rem;
    }

    /* Stats Cards */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s;
        border: 1px solid #e9ecef;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 65px;
        height: 65px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-icon.confirmed {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-icon.cancelled {
        background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    }

    .stat-info h3 {
        margin: 0;
        font-size: 14px;
        color: #6c757d;
        font-weight: 600;
    }

    .stat-number {
        margin: 8px 0 0;
        font-size: 28px;
        font-weight: bold;
        color: #1e3c72;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-group label {
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
    }

    .filter-select,
    .search-input {
        padding: 10px 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .filter-select:focus,
    .search-input:focus {
        outline: none;
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }

    .search-input {
        min-width: 250px;
    }

    .btn-refresh {
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-refresh:hover {
        background: #5a6268;
        transform: scale(1.05);
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }

    .table-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    .table-header h3 {
        margin: 0;
        color: #1e3c72;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-count {
        background: #e9ecef;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #495057;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead {
        background: #f1f3f4;
    }

    .admin-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.95rem;
    }

    .admin-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* User Info */
    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-info strong {
        color: #1e3c72;
        font-weight: 600;
    }

    .user-info small {
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 3px;
    }

    /* Price */
    .price {
        font-weight: 600;
        color: #28a745;
        font-family: 'Courier New', monospace;
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-transform: capitalize;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-confirmed {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-view,
    .btn-edit,
    .btn-delete {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 14px;
    }

    .btn-view {
        background: #17a2b8;
        color: white;
    }

    .btn-edit {
        background: #ffc107;
        color: white;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-view:hover {
        background: #138496;
        transform: scale(1.1);
    }

    .btn-edit:hover {
        background: #e0a800;
        transform: scale(1.1);
    }

    .btn-delete:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .empty-state i {
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state p {
        margin: 10px 0 0;
        font-size: 1.1rem;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin: 30px 0;
    }

    .page-btn,
    .page-number {
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-btn:hover:not(:disabled),
    .page-number:hover {
        background: #f8f9fa;
        border-color: #1e3c72;
    }

    .page-number.active {
        background: #1e3c72;
        color: white;
        border-color: #1e3c72;
    }

    /* Admin Footer */
    .admin-footer {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-size: 0.9rem;
        border-top: 1px solid #dee2e6;
        background: white;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 15px;
        width: 85%;
        max-width: 700px;
        position: relative;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .close-modal {
        position: absolute;
        right: 25px;
        top: 25px;
        font-size: 28px;
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s;
    }

    .close-modal:hover {
        color: #dc3545;
    }

    .modal-content h2 {
        color: #1e3c72;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .booking-detail {
        max-height: 60vh;
        overflow-y: auto;
        padding-right: 10px;
    }

    .detail-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-section:last-child {
        border-bottom: none;
    }

    .detail-section h3 {
        color: #495057;
        font-size: 1.1rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .detail-item label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .detail-item span {
        color: #212529;
        font-size: 1rem;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .btn-close-modal {
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-close-modal:hover {
        background: #5a6268;
    }

    .text-center {
        text-align: center;
    }

    /* Scrollbar Styling */
    .booking-detail::-webkit-scrollbar {
        width: 8px;
    }

    .booking-detail::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .booking-detail::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .booking-detail::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>