<?php
require_once '../config/database.php';
session_start(); // Pastikan session_start() ada
$page_title = "Booking Hotel";
require_once '../includes/header.php';

$conn = getConnection();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=booking');
    exit();
}

// Get parameters
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// Validate required parameters
if (!$hotel_id || !$check_in || !$check_out) {
    header('Location: hotels.php');
    exit();
}

// Fetch hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = ?";
$hotel_stmt = $conn->prepare($hotel_query);
$hotel_stmt->bind_param("i", $hotel_id);
$hotel_stmt->execute();
$hotel_result = $hotel_stmt->get_result();

if ($hotel_result->num_rows === 0) {
    echo "<div class='container'><p>Hotel tidak ditemukan. <a href='hotels.php'>Kembali</a></p></div>";
    require_once '../includes/footer.php';
    exit();
}

$hotel = $hotel_result->fetch_assoc();

// Calculate total nights and price
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$nights = $check_out_date->diff($check_in_date)->days;
$total_price = $hotel['price_per_night'] * $nights;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input
    $user_id = $_SESSION['user_id'];
    $guests = intval($_POST['guests']);

    // Insert booking sesuai struktur database
    $insert_query = "INSERT INTO bookings (user_id, hotel_id, check_in, check_out, guests, total_price, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param(
        "iissid",
        $user_id,
        $hotel_id,
        $check_in,
        $check_out,
        $guests,
        $total_price
    );

    if ($insert_stmt->execute()) {
        $booking_id = $conn->insert_id;
        header("Location: booking-confirmation.php?id=$booking_id");
        exit();
    } else {
        $error = "Gagal melakukan booking. Error: " . $conn->error;
    }

    $insert_stmt->close();
}
?>

<div class="container">
    <div class="page-header">
        <a href="javascript:history.back()" class="btn-back" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: #3498db; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1><i class="fas fa-calendar-check"></i> Form Pemesanan Hotel</h1>
        <p>Isi form berikut untuk melanjutkan pemesanan</p>
    </div>

    <div class="booking-container" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">
        <!-- Booking Summary -->
        <div class="booking-summary">
            <div class="summary-card" style="background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); padding: 25px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-receipt"></i> Ringkasan Pemesanan
                </h3>

                <div class="hotel-summary" style="display: flex; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                    <div class="hotel-image-small" style="width: 100px; height: 100px; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                        <img src="../img/<?php echo !empty($hotel['image_url']) ? htmlspecialchars($hotel['image_url']) : 'hotelNiagara.jpg'; ?>"
                            alt="<?php echo htmlspecialchars($hotel['name']); ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="hotel-info-summary">
                        <h4 style="margin: 0 0 8px 0; color: #2c3e50;"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                        <p class="location-small" style="margin: 0; color: #7f8c8d; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-map-marker-alt" style="font-size: 0.9rem;"></i>
                            <?php echo htmlspecialchars($hotel['location']); ?>
                        </p>
                        <div class="hotel-price" style="margin-top: 10px;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #e74c3c;">
                                Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?>
                            </span>
                            <span style="color: #7f8c8d;">/malam</span>
                        </div>
                    </div>
                </div>

                <div class="booking-details">
                    <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #eee;">
                        <span style="color: #7f8c8d;">Check-in</span>
                        <span style="font-weight: 500; color: #2c3e50;">
                            <?php echo date('d M Y', strtotime($check_in)); ?>
                        </span>
                    </div>

                    <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #eee;">
                        <span style="color: #7f8c8d;">Check-out</span>
                        <span style="font-weight: 500; color: #2c3e50;">
                            <?php echo date('d M Y', strtotime($check_out)); ?>
                        </span>
                    </div>

                    <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #eee;">
                        <span style="color: #7f8c8d;">Durasi</span>
                        <span style="font-weight: 500; color: #2c3e50;">
                            <?php echo $nights; ?> malam
                        </span>
                    </div>

                    <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #eee;">
                        <span style="color: #7f8c8d;">Jumlah Tamu</span>
                        <span style="font-weight: 500; color: #2c3e50;" id="guests-display">
                            <?php echo $guests; ?> orang
                        </span>
                    </div>
                </div>

                <div class="price-summary" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                    <div class="price-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">Harga per malam</span>
                        <span style="color: #666;">
                            Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="price-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">× <?php echo $nights; ?> malam</span>
                        <span style="color: #666;">
                            Rp <?php echo number_format($hotel['price_per_night'] * $nights, 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="total-price" style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px solid #ddd; font-weight: bold; font-size: 1.2rem;">
                        <span style="color: #2c3e50;">Total Harga</span>
                        <span style="color: #e74c3c;" id="total-price-display">
                            Rp <?php echo number_format($total_price, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="booking-form-container">
            <div class="form-card" style="background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); padding: 30px;">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error" style="background: #ffeaea; color: #c0392b; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #c0392b;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <h3 style="color: #2c3e50; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-circle"></i> Data Pemesan
                </h3>
                <p style="color: #7f8c8d; margin-bottom: 25px;">Harap periksa dan lengkapi data pemesan berikut.</p>

                <form method="POST" action="" class="booking-data-form">
                    <!-- Data dari session user -->
                    <div class="user-info-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                        <h4 style="color: #2c3e50; margin-bottom: 15px;">Informasi Akun</h4>
                        <div class="user-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">Nama</label>
                                <input type="text"
                                    value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Tidak ada data'); ?>"
                                    disabled
                                    style="width: 100%; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 5px; color: #666;">
                            </div>
                            <div>
                                <label style="display: block; color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">Email</label>
                                <input type="text"
                                    value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'Tidak ada data'); ?>"
                                    disabled
                                    style="width: 100%; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 5px; color: #666;">
                            </div>
                        </div>
                        <p style="color: #95a5a6; font-size: 0.9rem; margin-top: 10px; margin-bottom: 0;">
                            <i class="fas fa-info-circle"></i> Data ini diambil dari profil akun Anda.
                        </p>
                    </div>

                    <!-- Form input -->
                    <div class="form-section">
                        <h4 style="color: #2c3e50; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #3498db;">Detail Pemesanan</h4>

                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                        <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="guests" style="display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50;">
                                <i class="fas fa-users"></i> Jumlah Tamu *
                            </label>
                            <select id="guests" name="guests" required
                                onchange="updateGuestsDisplay(this.value)"
                                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; background: white;">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $guests ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> <?php echo $i == 1 ? 'orang' : 'orang'; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                                * Maksimal 10 orang per pemesanan
                            </small>
                        </div>

                        <div class="form-group" style="margin-bottom: 30px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50;">
                                <i class="fas fa-calendar-alt"></i> Periode Menginap
                            </label>
                            <div class="date-display" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                    <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">Check-in</div>
                                    <div style="font-size: 1.1rem; font-weight: 500; color: #2c3e50;">
                                        <?php echo date('d M Y', strtotime($check_in)); ?>
                                    </div>
                                </div>
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                    <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">Check-out</div>
                                    <div style="font-size: 1.1rem; font-weight: 500; color: #2c3e50;">
                                        <?php echo date('d M Y', strtotime($check_out)); ?>
                                    </div>
                                </div>
                            </div>
                            <p style="color: #95a5a6; font-size: 0.9rem; margin-top: 10px; margin-bottom: 0;">
                                Total menginap: <strong><?php echo $nights; ?> malam</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="terms-section" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <div class="terms-group" style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px;">
                            <input type="checkbox" id="terms" name="terms" required
                                style="margin-top: 3px; flex-shrink: 0;">
                            <label for="terms" style="font-size: 0.95rem; color: #2c3e50; line-height: 1.5;">
                                Saya telah membaca dan menyetujui
                                <a href="terms.php" target="_blank" style="color: #3498db; text-decoration: none;">syarat dan ketentuan</a>
                                serta
                                <a href="privacy.php" target="_blank" style="color: #3498db; text-decoration: none;">kebijakan privasi</a>
                                yang berlaku. Saya memahami bahwa pemesanan ini mengikat dan pembatalan mungkin dikenakan biaya.
                            </label>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <div class="action-info">
                            <p style="color: #7f8c8d; font-size: 0.9rem; margin: 0;">
                                <i class="fas fa-shield-alt"></i> Data Anda aman dan terlindungi
                            </p>
                        </div>

                        <div class="action-buttons" style="display: flex; gap: 15px;">
                            <button type="button" class="btn-cancel" onclick="history.back()"
                                style="padding: 12px 25px; background: #e0e0e0; color: #666; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 500;">
                                <i class="fas fa-times"></i> Batal
                            </button>
                            <button type="submit" class="btn-confirm-booking"
                                style="padding: 12px 30px; background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 10px; transition: transform 0.2s;">
                                <i class="fas fa-lock"></i> Konfirmasi Pemesanan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk update display jumlah tamu dan total harga
    function updateGuestsDisplay(guests) {
        // Update display jumlah tamu
        document.getElementById('guests-display').textContent = guests + ' orang';

        // Note: Dalam struktur database Anda, total_price hanya berdasarkan harga hotel × malam
        // Jumlah tamu tidak mempengaruhi harga (tidak ada field rooms atau extra guest charge)
        // Jadi total harga tetap sama
    }

    // Validasi form sebelum submit
    document.querySelector('.booking-data-form').addEventListener('submit', function(e) {
        const termsCheckbox = document.getElementById('terms');

        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Anda harus menyetujui syarat dan ketentuan sebelum melanjutkan.');
            return false;
        }

        return true;
    });

    // Efek hover untuk tombol
    const confirmBtn = document.querySelector('.btn-confirm-booking');
    confirmBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 5px 15px rgba(46, 204, 113, 0.3)';
    });

    confirmBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });
</script>

<?php
$hotel_stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>