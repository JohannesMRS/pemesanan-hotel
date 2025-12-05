<?php
require_once '../config/database.php';
$page_title = "Hotel";
require_once '../includes/header.php';

$conn = getConnection();

// Handle search
$search_location = isset($_GET['location']) ? $_GET['location'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// Build query
$query = "SELECT * FROM hotels WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_location)) {
    $query .= " AND (location LIKE ? OR name LIKE ?)";
    $search_term = "%$search_location%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Get hotels
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-hotel"></i> Daftar Hotel</h1>
        <p>Temukan hotel terbaik di sekitar Danau Toba</p>
    </div>
    
    <!-- Search Filter -->
    <div class="filter-box">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="location" placeholder="Cari lokasi..." 
                           value="<?php echo htmlspecialchars($search_location); ?>">
                </div>
                
                <div class="filter-group">
                    <select name="sort_by">
                        <option value="price_asc">Harga: Rendah ke Tinggi</option>
                        <option value="price_desc">Harga: Tinggi ke Rendah</option>
                        <option value="name_asc">Nama: A-Z</option>
                        <option value="name_desc">Nama: Z-A</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Hotels Grid -->
    <div class="hotels-container">
        <?php if ($result->num_rows > 0): ?>
            <div class="hotels-grid">
                <?php while($hotel = $result->fetch_assoc()): 
                    $amenities = json_decode($hotel['amenities'], true);
                ?>
                <div class="hotel-item">
                    <div class="hotel-card-large">
                        <div class="hotel-image">
                            <img src="../assets/images/hotels/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                            <?php if($hotel['is_recommended']): ?>
                                <div class="recommended-badge">Rekomendasi</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="hotel-details">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p class="location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($hotel['location']); ?>
                            </p>
                            
                            <div class="description">
                                <?php echo substr(htmlspecialchars($hotel['description']), 0, 150); ?>...
                            </div>
                            
                            <div class="amenities-list">
                                <h4>Fasilitas:</h4>
                                <div class="amenities">
                                    <?php if ($amenities): ?>
                                        <?php foreach(array_slice($amenities, 0, 4) as $amenity): ?>
                                            <span class="amenity">
                                                <i class="fas fa-check-circle"></i> 
                                                <?php echo htmlspecialchars($amenity); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="hotel-footer">
                                <div class="price-box">
                                    <span class="price-label">Mulai dari</span>
                                    <div class="price">
                                        Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?>
                                        <span>/malam</span>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <a href="hotel-detail.php?id=<?php echo $hotel['id']; ?>" 
                                       class="btn-detail">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </a>
                                    <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>" 
                                       class="btn-book-now">
                                        <i class="fas fa-calendar-check"></i> Pesan Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-hotels">
                <i class="fas fa-hotel fa-4x"></i>
                <h3>Hotel tidak ditemukan</h3>
                <p>Coba gunakan kata kunci pencarian yang berbeda</p>
                <a href="hotels.php" class="btn-reset">Reset Pencarian</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>