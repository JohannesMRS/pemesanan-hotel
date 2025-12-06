<?php
require_once '../config/database.php';
$page_title = "Home";
require_once '../includes/header.php';
$conn = getConnection();
?>

<!-- Hero Section with Background -->
<section class="hero">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Selamat Datang di Danau Toba</h1>
            <p>Temukan pengalaman menginap terbaik di sekitar Danau Toba</p>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <div class="search-box">
            <h2><i class="fas fa-search"></i> Cari Hotel Impian Anda</h2>
            <form action="pages/hotels.php" method="GET" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                        <input type="text" id="location" name="location" placeholder="Misal: Parapat, Samosir">
                    </div>

                    <div class="form-group">
                        <label for="check-in"><i class="fas fa-calendar-alt"></i> Check-in</label>
                        <input type="date" id="check-in" name="check_in">
                    </div>

                    <div class="form-group">
                        <label for="check-out"><i class="fas fa-calendar-alt"></i> Check-out</label>
                        <input type="date" id="check-out" name="check_out">
                    </div>

                    <div class="form-group">
                        <label for="guests"><i class="fas fa-user-friends"></i> Jumlah Tamu</label>
                        <select id="guests" name="guests">
                            <option value="1">1 Orang</option>
                            <option value="2" selected>2 Orang</option>
                            <option value="3">3 Orang</option>
                            <option value="4">4 Orang</option>
                            <option value="5">5+ Orang</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Cari Hotel
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Recommended Hotels -->
<section class="recommended-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-star"></i> Hotel Rekomendasi</h2>
            <p>Hotel terbaik pilihan kami di sekitar Danau Toba</p>
        </div>

        <div class="hotel-grid">
            <?php
            $query = "SELECT * FROM hotels WHERE is_recommended = 1 LIMIT 4";
            $result = $conn->query($query);

            if ($result->num_rows > 0):
                while ($hotel = $result->fetch_assoc()):
                    $amenities = json_decode($hotel['amenities'], true);
            ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <img src="img/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                            <div class="hotel-badge">Rekomendasi</div>
                        </div>

                        <div class="hotel-info">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p class="hotel-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($hotel['location']); ?>
                            </p>

                            <div class="hotel-amenities">
                                <?php if ($amenities): ?>
                                    <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                        <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="hotel-price">
                                <span class="price">Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?></span>
                                <span class="price-label">/malam</span>
                            </div>

                            <div class="hotel-actions">
                                <a href="pages/hotel-detail.php?id=<?php echo $hotel['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="pages/booking.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn-book">
                                    <i class="fas fa-calendar-check"></i> Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="no-data">
                    <i class="fas fa-hotel fa-3x"></i>
                    <p>Belum ada hotel rekomendasi</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="view-all">
            <a href="hotels.php" class="btn-view-all">
                <i class="fas fa-hotel"></i> Lihat Semua Hotel
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Aman & Terpercaya</h3>
                <p>Transaksi aman dengan sistem pembayaran terpercaya</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Customer service siap membantu kapan saja</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-tags"></i>
                <h3>Harga Terbaik</h3>
                <p>Garansi harga terbaik di Danau Toba</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-award"></i>
                <h3>Hotel Berkualitas</h3>
                <p>Hotel pilihan dengan fasilitas lengkap</p>
            </div>
        </div>
    </div>
</section>

<?php
$conn->close();
require_once '../includes/footer.php';
?>