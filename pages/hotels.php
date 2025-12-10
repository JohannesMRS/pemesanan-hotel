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
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

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

// Add sorting
if (!empty($sort_by)) {
    switch ($sort_by) {
        case 'price_asc':
            $query .= " ORDER BY price_per_night ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY price_per_night DESC";
            break;
        case 'name_asc':
            $query .= " ORDER BY name ASC";
            break;
        case 'name_desc':
            $query .= " ORDER BY name DESC";
            break;
        default:
            $query .= " ORDER BY is_recommended DESC, name ASC";
    }
} else {
    $query .= " ORDER BY is_recommended DESC, name ASC";
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
                        <option value="">Urutkan</option>
                        <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                        <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                        <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Nama: A-Z</option>
                        <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Nama: Z-A</option>
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
                <?php while ($hotel = $result->fetch_assoc()):
                    $amenities = json_decode($hotel['amenities'], true);
                ?>
                    <div class="hotel-item">
                        <div class="hotel-card-large">
                            <div class="hotel-image">
                                <img src="../img/<?php echo $hotel['image_url'] ?: 'hotelNiagara.jpg'; ?>"
                                    alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                <?php if ($hotel['is_recommended']): ?>
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
                                            <?php foreach (array_slice($amenities, 0, 4) as $amenity): ?>
                                                <span class="amenity">
                                                    <i class="fas fa-check-circle"></i>
                                                    <?php echo htmlspecialchars($amenity); ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($amenities) > 4): ?>
                                                <span class="amenity-more" onclick="showHotelDetail(<?php echo $hotel['id']; ?>)">
                                                    +<?php echo count($amenities) - 4; ?> lainnya
                                                </span>
                                            <?php endif; ?>
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
                                        <button type="button" class="btn-detail"
                                            onclick="showHotelDetail(<?php echo $hotel['id']; ?>)">
                                            <i class="fas fa-info-circle"></i> Detail
                                        </button>
                                        <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>"
                                            class="btn-book-now">
                                            <i class="fas fa-calendar-check"></i> Pesan Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal untuk detail hotel -->
                    <div id="hotelModal<?php echo $hotel['id']; ?>" class="hotel-modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2><?php echo htmlspecialchars($hotel['name']); ?></h2>
                                <button class="close-modal" onclick="closeHotelDetail(<?php echo $hotel['id']; ?>)">
                                    &times;
                                </button>
                            </div>

                            <div class="modal-body">
                                <div class="modal-image">
                                    <img src="../img/<?php echo $hotel['image_url'] ?: 'hotelNiagara.jpg'; ?>"
                                        alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                    <?php if ($hotel['is_recommended']): ?>
                                        <div class="recommended-badge-modal">Rekomendasi</div>
                                    <?php endif; ?>
                                </div>

                                <div class="modal-details">
                                    <div class="modal-section">
                                        <h3><i class="fas fa-map-marker-alt"></i> Lokasi</h3>
                                        <p><?php echo htmlspecialchars($hotel['location']); ?></p>
                                    </div>

                                    <div class="modal-section">
                                        <h3><i class="fas fa-align-left"></i> Deskripsi</h3>
                                        <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                                    </div>

                                    <div class="modal-section">
                                        <h3><i class="fas fa-wifi"></i> Fasilitas Lengkap</h3>
                                        <div class="modal-amenities">
                                            <?php if ($amenities): ?>
                                                <?php foreach ($amenities as $amenity): ?>
                                                    <div class="modal-amenity">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span><?php echo htmlspecialchars($amenity); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="modal-section">
                                        <h3><i class="fas fa-tag"></i> Harga</h3>
                                        <div class="modal-price">
                                            <div class="price-large">
                                                Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?>
                                                <span>/malam</span>
                                            </div>
                                            <p class="price-note">*Harga sudah termasuk pajak</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn-modal-close" onclick="closeHotelDetail(<?php echo $hotel['id']; ?>)">
                                    Tutup
                                </button>
                                <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>"
                                    class="btn-modal-book">
                                    <i class="fas fa-calendar-check"></i> Pesan Sekarang
                                </a>
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

<!-- Modal Styling dan JavaScript -->
<style>
    /* Modal Styles */
    .hotel-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        animation: fadeIn 0.3s ease-in-out;
        overflow-y: auto;
    }

    .modal-content {
        background-color: white;
        margin: 50px auto;
        width: 90%;
        max-width: 800px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.4s ease-out;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 30px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.8rem;
    }

    .close-modal {
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }

    .close-modal:hover {
        transform: scale(1.2);
    }

    .modal-body {
        padding: 30px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-image {
        position: relative;
        margin-bottom: 25px;
        border-radius: 10px;
        overflow: hidden;
    }

    .modal-image img {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }

    .recommended-badge-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #e74c3c;
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .modal-section {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }

    .modal-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .modal-section h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-amenities {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }

    .modal-amenity {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: transform 0.2s;
    }

    .modal-amenity:hover {
        transform: translateY(-2px);
        background: #e9ecef;
    }

    .modal-amenity i {
        color: #2ecc71;
    }

    .modal-price {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .modal-price .price-large {
        font-size: 2.5rem;
        font-weight: bold;
        color: #e74c3c;
        margin-bottom: 10px;
    }

    .modal-price .price-large span {
        font-size: 1rem;
        color: #7f8c8d;
    }

    .price-note {
        color: #95a5a6;
        font-size: 0.9rem;
        margin: 0;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 20px 30px;
        background: #f8f9fa;
        border-radius: 0 0 15px 15px;
        border-top: 1px solid #eee;
    }

    .btn-modal-close {
        padding: 12px 30px;
        background: #95a5a6;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s;
    }

    .btn-modal-close:hover {
        background: #7f8c8d;
    }

    .btn-modal-book {
        padding: 12px 30px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-modal-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        color: white;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Amenity "lebih banyak" link */
    .amenity-more {
        color: #3498db;
        cursor: pointer;
        font-size: 0.9rem;
        text-decoration: underline;
        margin-left: 10px;
    }

    .amenity-more:hover {
        color: #2980b9;
    }

    /* Button detail style */
    .btn-detail {
        padding: 10px 20px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        transition: background-color 0.3s;
    }

    .btn-detail:hover {
        background: #2980b9;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 20px auto;
        }

        .modal-header h2 {
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-amenities {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn-modal-close,
        .btn-modal-book {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Fungsi untuk menampilkan modal detail hotel
    function showHotelDetail(hotelId) {
        const modal = document.getElementById('hotelModal' + hotelId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
    }

    // Fungsi untuk menutup modal detail hotel
    function closeHotelDetail(hotelId) {
        const modal = document.getElementById('hotelModal' + hotelId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Enable scrolling
        }
    }

    // Tutup modal saat klik di luar konten modal
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('hotel-modal');
        for (let modal of modals) {
            if (event.target == modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    }

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.getElementsByClassName('hotel-modal');
            for (let modal of modals) {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        }
    });
</script>

<?php
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>