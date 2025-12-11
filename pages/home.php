<?php
require_once '../config/database.php';
$page_title = "Home";
require_once '../includes/header.php';
$conn = getConnection();
?>

<section class="hero">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Selamat Datang di Danau Toba</h1>
            <p>Temukan pengalaman menginap terbaik di sekitar Danau Toba</p>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="container">
        <div class="search-box">
            <h2><i class="fas fa-search"></i> Cari Hotel Impian Anda</h2>
            <form action="hotels.php" method="GET" class="search-form">
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
                    // Decode amenities for display
                    $amenities = json_decode($hotel['amenities'], true);
            ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <img src="../img/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>"
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
                                    <?php if (count($amenities) > 3): ?>
                                        <span class="amenity-more"
                                            onclick="showHotelDetail(<?php echo $hotel['id']; ?>)">
                                            +<?php echo count($amenities) - 3; ?> lainnya
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="hotel-price">
                                <span class="price">Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?></span>
                                <span class="price-label">/malam</span>
                            </div>

                            <div class="hotel-actions">
                                <button type="button" class="btn-view btn-detail"
                                    onclick="showHotelDetail(<?php echo $hotel['id']; ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn-book">
                                    <i class="fas fa-calendar-check"></i> Pesan
                                </a>
                            </div>
                        </div>
                    </div>

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
                                    <img src="../img/<?php echo $hotel['image_url'] ?: 'default.jpg'; ?>"
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
                                            <?php else: ?>
                                                <p>Tidak ada fasilitas yang tercatat.</p>
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
                                            <p class="price-note">*Harga dapat berubah sewaktu-waktu</p>
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

---

<style>
    /* Modal Styles */
    .hotel-modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        /* Black with opacity */
        z-index: 1000;
        overflow-y: auto;
        /* Enable scroll if content is too long */
        padding: 20px 0;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 50px auto;
        /* 50px from top and centered */
        width: 90%;
        max-width: 800px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.3s ease-in-out;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px;
        background: #3498db;
        /* Blue header */
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
    }

    .close-modal {
        color: white;
        font-size: 2rem;
        font-weight: bold;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0 5px;
        transition: color 0.2s;
    }

    .close-modal:hover,
    .close-modal:focus {
        color: #ddd;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-body {
        padding: 20px 25px;
    }

    .modal-image {
        position: relative;
        margin-bottom: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    .modal-image img {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: cover;
    }

    .recommended-badge-modal {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e74c3c;
        color: white;
        padding: 5px 15px;
        border-radius: 15px;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .modal-section {
        margin-bottom: 20px;
    }

    .modal-section h3 {
        font-size: 1.2rem;
        color: #2c3e50;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
    }

    .modal-amenities {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .modal-amenity {
        display: flex;
        align-items: center;
        background: #ecf0f1;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 0.9rem;
    }

    .modal-amenity i {
        color: #2ecc71;
        margin-right: 5px;
    }

    .modal-price {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: right;
    }

    .price-large {
        font-size: 1.8rem;
        font-weight: bold;
        color: #e74c3c;
    }

    .price-large span {
        font-size: 0.9rem;
        font-weight: normal;
        color: #7f8c8d;
    }

    .price-note {
        font-size: 0.8rem;
        color: #95a5a6;
        margin: 5px 0 0;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        padding: 15px 25px;
        gap: 10px;
        border-top: 1px solid #eee;
        border-radius: 0 0 10px 10px;
    }

    .btn-modal-close {
        padding: 10px 20px;
        background-color: #95a5a6;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 1rem;
    }

    .btn-modal-close:hover {
        background-color: #7f8c8d;
    }

    .btn-modal-book {
        padding: 10px 20px;
        background-color: #2ecc71;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        transition: background-color 0.3s;
    }

    .btn-modal-book:hover {
        background-color: #27ae60;
        color: white;
    }

    .btn-detail {
        /* New style for the detail button in the card */
        background-color: #3498db;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.3s;
        font-size: 0.9rem;
    }

    .btn-detail:hover {
        background-color: #2980b9;
    }

    .amenity-more {
        color: #3498db;
        cursor: pointer;
        font-size: 0.9rem;
        text-decoration: underline;
        margin-left: 5px;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>

<script>
    // Fungsi untuk menampilkan modal detail hotel
    function showHotelDetail(hotelId) {
        const modal = document.getElementById('hotelModal' + hotelId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Nonaktifkan scroll di background
        }
    }

    // Fungsi untuk menutup modal detail hotel
    function closeHotelDetail(hotelId) {
        const modal = document.getElementById('hotelModal' + hotelId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Aktifkan scroll kembali
        }
    }

    // Tutup modal jika user klik di luar area modal
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('hotel-modal');
        for (let modal of modals) {
            if (event.target == modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    }
</script>


<?php
// Tutup koneksi di akhir skrip
$conn->close();
require_once '../includes/footer.php';
?>