<?php
require_once '../../config/database.php';
requireAdmin();

$conn = getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: index.php?success=Hotel berhasil dihapus');
    exit();
}

// Get all hotels with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_hotels = $conn->query("SELECT COUNT(*) as count FROM hotels")->fetch_assoc()['count'];
$total_pages = ceil($total_hotels / $limit);

$hotels = $conn->query("
    SELECT h.*, u.username as created_by_name 
    FROM hotels h 
    LEFT JOIN users u ON h.created_by = u.id 
    ORDER BY h.created_at DESC 
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hotel - Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-hotel"></i> Kelola Hotel</h1>
                <a href="add.php" class="btn-add">
                    <i class="fas fa-plus"></i> Tambah Hotel Baru
                </a>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Hotels Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Hotel</h3>
                    <div class="search-box">
                        <input type="text" id="searchHotels" placeholder="Cari hotel...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gambar</th>
                                <th>Nama Hotel</th>
                                <th>Lokasi</th>
                                <th>Harga/Malam</th>
                                <th>Rekomendasi</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hotels)): ?>
                                <?php foreach($hotels as $hotel): ?>
                                <tr>
                                    <td>#<?php echo str_pad($hotel['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <img src="../../assets/images/hotels/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($hotel['name']); ?>"
                                             class="table-image">
                                    </td>
                                    <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                    <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                                    <td>Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if($hotel['is_recommended']): ?>
                                            <span class="badge badge-success">Ya</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Tidak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($hotel['created_by_name'] ?? 'System'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($hotel['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $hotel['id']; ?>" 
                                               class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $hotel['id']; ?>" 
                                               class="btn-action btn-delete" 
                                               title="Hapus"
                                               onclick="return confirm('Hapus hotel ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="../../pages/hotel-detail.php?id=<?php echo $hotel['id']; ?>" 
                                               class="btn-action btn-view" 
                                               title="Lihat" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="no-data">
                                        <i class="fas fa-hotel"></i>
                                        <p>Belum ada hotel. <a href="add.php">Tambah hotel baru</a></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="page-link">
                            Berikutnya <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // Search functionality
    document.getElementById('searchHotels').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>