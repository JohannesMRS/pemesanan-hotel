<?php
// Baris ini memanggil session_start(), getConnection(), isLoggedIn(), dan tag pembuka HTML/HEAD/BODY
$page_title = "Kontak";
require_once '../includes/header.php';

// Koneksi ke database
$conn = getConnection();

// -------------------------------------------------------------------------
// 1. Handle feedback submission (Hanya jika POST request dan user login)
// -------------------------------------------------------------------------
$feedback_success = false;
$feedback_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    // Ambil data dari form
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    // Pastikan pengguna sudah login sebelum memproses feedback
    if (isLoggedIn()) {
        // user_id pasti ada di sesi karena isLoggedIn() bernilai TRUE
        $user_id = $_SESSION['id'];

        // Query untuk INSERT data feedback
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $subject, $message, $rating);

        if ($stmt->execute()) {
            $feedback_success = true;
        } else {
            $feedback_error = "Gagal mengirim feedback. Silakan coba lagi.";
        }
        $stmt->close();
    } else {
        $feedback_error = "Anda harus login untuk mengirim feedback.";
    }
}

// -------------------------------------------------------------------------
// 2. Ambil semua feedback dari database (Untuk tampilan - Selalu terlihat)
// -------------------------------------------------------------------------
$feedbacks = [];
// JOIN dengan tabel users untuk mendapatkan username
$sql_select_feedback = "SELECT 
                            f.subject, 
                            f.message, 
                            f.rating, 
                            f.created_at, 
                            u.username 
                        FROM 
                            feedback f
                        LEFT JOIN 
                            users u ON f.user_id = u.id 
                        ORDER BY 
                            f.created_at DESC 
                        LIMIT 10";

$result_feedback = $conn->query($sql_select_feedback);

if ($result_feedback) {
    while ($row = $result_feedback->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

// Koneksi ditutup di sini
$conn->close();
?>

<style>
    /* Variabel Warna */
    :root {
        --primary-color: #34495e;
        --accent-color: #4f92d9ff;
        --light-bg: #f5f7f9;
        --card-bg: #ffffff;
        --success-color: #4f92d9ff;
        --rating-color: #ffd700;
        --star-inactive: #e0e0e0;
        --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-card: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        padding-top: 30px;
        padding-bottom: 60px;
    }

    /* Header Halaman */
    .page-header {
        text-align: center;
        padding: 50px 0;
        margin-bottom: 50px;
        background: var(--card-bg);
        border-radius: 15px;
        box-shadow: var(--shadow-card);
        border-top: 5px solid var(--primary-color);
    }

    .page-header h1 {
        color: var(--primary-color);
        font-weight: 800;
        font-size: 2.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header h1 i {
        margin-right: 15px;
        color: var(--accent-color);
        font-size: 2.5rem;
    }

    .page-header p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-top: 10px;
    }

    /* Layout Grid Baru - Alamat dan Telepon di atas Form */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }

    /* Kontainer untuk Alamat dan Telepon */
    .contact-info-container {
        grid-column: span 2;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-bottom: 20px;
    }

    /* Formulir Feedback */
    .feedback-section {
        grid-column: span 2;
        background: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: var(--shadow-card);
        border: 2px solid #e6f2ff;
        border-top: 5px solid var(--accent-color);
    }

    /* Card untuk Alamat dan Telepon */
    .info-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: var(--shadow-light);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid #e9ecef;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .info-card i {
        font-size: 2.5rem;
        color: var(--accent-color);
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 50%;
        background: rgba(0, 123, 255, 0.1);
    }

    .info-card h3 {
        color: var(--primary-color);
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .info-card p {
        color: #6c757d;
        line-height: 1.6;
        margin: 0;
    }

    /* Feedback Form */
    .feedback-section h2 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
        padding-bottom: 15px;
        border-bottom: 2px solid #e6f2ff;
        position: relative;
    }

    .feedback-section h2:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: var(--accent-color);
    }

    .feedback-form label {
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 8px;
        display: block;
    }

    .feedback-form .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }

    .feedback-form .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Styling untuk star rating system */
    .star-rating-container {
        margin: 20px 0;
    }

    .star-rating-label {
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 15px;
        display: block;
        font-size: 1.1rem;
    }

    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 5px;
        margin-bottom: 10px;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        font-size: 2.5rem;
        color: var(--star-inactive);
        cursor: pointer;
        transition: all 0.2s;
        padding: 5px;
        margin: 0;
        position: relative;
    }

    .star-rating label:hover,
    .star-rating label:hover~label {
        color: #ffcc00;
        transform: scale(1.1);
    }

    .star-rating input:checked~label {
        color: var(--rating-color);
    }

    .star-rating input:checked+label:hover,
    .star-rating input:checked+label:hover~label,
    .star-rating input:checked~label:hover,
    .star-rating input:checked~label:hover~label {
        color: #ffcc00;
    }

    /* Rating text display */
    .rating-text {
        text-align: center;
        margin-top: 10px;
        font-size: 1rem;
        color: var(--primary-color);
        min-height: 24px;
    }

    .rating-value-display {
        font-weight: 600;
        color: var(--accent-color);
    }

    /* Hidden input untuk rating */
    #rating-value {
        display: none;
    }

    /* Tombol Kirim berwarna biru */
    .feedback-form .btn-primary {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        border-radius: 25px;
        padding: 12px 35px;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
        width: 100%;
        transition: all 0.3s;
        margin-top: 20px;
    }

    .feedback-form .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 6px 10px rgba(0, 123, 255, 0.4);
    }

    .feedback-form .btn-primary:disabled {
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Notifikasi Login */
    .alert-info.login-prompt {
        border-left: 5px solid var(--primary-color);
        background: #f8f9fa;
        color: #343a40;
        padding: 30px;
        text-align: center;
    }

    .login-prompt h4 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
    }

    .login-prompt .btn-primary {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
    }

    /* 3. All Feedbacks Display Section - Desain seperti di gambar */
    .all-feedbacks-section {
        grid-column: span 2;
        margin-top: 60px;
    }

    .all-feedbacks-section h2 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
        font-size: 2rem;
    }

    /* Grid untuk feedback cards */
    .feedbacks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .feedback-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 25px;
        box-shadow: var(--shadow-light);
        border: 1px solid #e9ecef;
        transition: transform 0.3s;
        position: relative;
        overflow: hidden;
    }

    .feedback-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .feedback-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--accent-color);
    }

    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .feedback-subject {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.2rem;
        margin: 0;
        flex: 1;
    }

    .feedback-rating {
        color: var(--rating-color);
        font-size: 1.1rem;
        margin-left: 10px;
    }

    .feedback-message {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        padding: 10px 0;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .feedback-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .feedback-user {
        font-weight: 600;
        color: var(--accent-color);
    }

    .feedback-date {
        color: #6c757d;
        text-align: right;
    }

    /* Styling untuk bintang di feedback yang sudah dikirim */
    .feedback-rating-stars {
        display: flex;
        gap: 3px;
        margin-bottom: 10px;
    }

    .star-filled {
        color: var(--rating-color);
    }

    .star-empty {
        color: #e0e0e0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .contact-info-container {
            grid-template-columns: 1fr;
            grid-column: span 1;
        }

        .feedback-section,
        .all-feedbacks-section {
            grid-column: span 1;
        }

        .feedbacks-grid {
            grid-template-columns: 1fr;
        }

        .page-header h1 {
            font-size: 2.2rem;
        }

        .star-rating label {
            font-size: 2rem;
        }
    }
</style>

<script>
    // JavaScript untuk sistem rating bintang
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-rating input');
        const ratingValue = document.getElementById('rating-value');
        const ratingText = document.getElementById('rating-text');
        const submitBtn = document.querySelector('button[name="submit_feedback"]');

        // Teks untuk setiap rating
        const ratingMessages = {
            1: "Buruk - Sangat tidak puas",
            2: "Kurang - Tidak puas",
            3: "Cukup - Biasa saja",
            4: "Baik - Puas",
            5: "Sangat Baik - Sangat puas"
        };

        // Fungsi untuk update teks rating
        function updateRatingText(value) {
            if (value > 0) {
                ratingText.innerHTML = `Rating Anda: <span class="rating-value-display">${value} bintang</span> - ${ratingMessages[value]}`;
                ratingValue.value = value;
                submitBtn.disabled = false;
            } else {
                ratingText.innerHTML = 'Klik bintang untuk memberikan rating';
                ratingValue.value = '';
                submitBtn.disabled = true;
            }
        }

        // Event listener untuk setiap bintang
        stars.forEach(star => {
            star.addEventListener('change', function() {
                const selectedValue = this.value;
                updateRatingText(parseInt(selectedValue));
            });

            star.addEventListener('mouseover', function() {
                const hoverValue = this.value;
                if (!this.checked) {
                    ratingText.innerHTML = `Berikan ${hoverValue} bintang - ${ratingMessages[hoverValue]}`;
                }
            });
        });

        // Reset rating text saat mouse leave
        document.querySelector('.star-rating').addEventListener('mouseleave', function() {
            const checkedStar = document.querySelector('.star-rating input:checked');
            if (checkedStar) {
                updateRatingText(parseInt(checkedStar.value));
            } else {
                ratingText.innerHTML = 'Klik bintang untuk memberikan rating';
            }
        });

        // Validasi form sebelum submit
        const form = document.querySelector('.feedback-form');
        form.addEventListener('submit', function(e) {
            if (!ratingValue.value || ratingValue.value === '') {
                e.preventDefault();
                ratingText.innerHTML = '<span style="color: #dc3545;">Pilih rating terlebih dahulu!</span>';
                ratingText.style.color = '#dc3545';
                return false;
            }
        });

        // Inisialisasi awal
        updateRatingText(0);
    });
</script>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-envelope"></i> Kontak Kami</h1>
        <p>Hubungi kami untuk pertanyaan, saran, atau kirim ulasan Anda</p>
    </div>

    <div class="contact-grid">
        <!-- Kontainer untuk Alamat dan Telepon di atas Form -->
        <div class="contact-info-container">
            <div class="info-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Alamat Kantor</h3>
                <p>Jl. Danau Toba No. 123<br>Medan, Sumatera Utara<br>Indonesia 20154</p>
            </div>

            <div class="info-card">
                <i class="fas fa-phone"></i>
                <h3>Telepon & Email</h3>
                <p>(061) 12345678<br>info@tikethotel.com</p>
            </div>
        </div>

        <!-- Formulir Feedback -->
        <div class="feedback-section">
            <h2>Kiriman Ulasan & Feedback Anda</h2>

            <?php if ($feedback_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <strong>Berhasil!</strong> Terima kasih! Feedback Anda berhasil dikirim.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif ($feedback_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($feedback_error) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <form action="contact.php" method="POST" class="feedback-form">
                    <div class="form-group">
                        <label for="subject">Subjek</label>
                        <input type="text" class="form-control" id="subject" name="subject" required maxlength="100" placeholder="Masukkan subjek feedback Anda">
                    </div>

                    <div class="form-group">
                        <label for="message">Pesan / Saran</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required placeholder="Tulis pesan atau saran Anda di sini..."></textarea>
                    </div>

                    <div class="form-group star-rating-container">
                        <span class="star-rating-label">Rating (klik bintang untuk memilih):</span>

                        <!-- Star Rating System -->
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5">
                            <label for="star5" title="5 bintang - Sangat Baik">
                                <i class="fas fa-star"></i>
                            </label>

                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4" title="4 bintang - Baik">
                                <i class="fas fa-star"></i>
                            </label>

                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3" title="3 bintang - Cukup">
                                <i class="fas fa-star"></i>
                            </label>

                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2" title="2 bintang - Kurang">
                                <i class="fas fa-star"></i>
                            </label>

                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1" title="1 bintang - Buruk">
                                <i class="fas fa-star"></i>
                            </label>
                        </div>

                        <!-- Hidden input untuk menyimpan nilai rating -->
                        <input type="hidden" id="rating-value" name="rating" value="">

                        <!-- Display rating text -->
                        <div class="rating-text" id="rating-text">
                            Klik bintang untuk memberikan rating
                        </div>
                    </div>

                    <button type="submit" name="submit_feedback" class="btn btn-primary" disabled>
                        <i class="fas fa-paper-plane"></i> Kirim Feedback
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info text-center login-prompt">
                    <h4><i class="fas fa-lock"></i> Fitur Khusus Pengguna Terdaftar</h4>
                    <p class="mb-3">Anda harus <strong>Login</strong> terlebih dahulu untuk dapat mengirim ulasan dan feedback.</p>
                    <a href="../auth/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tampilan Feedback dari Pengguna -->
        <div class="all-feedbacks-section">
            <hr class="mb-5">
            <h2><i class="fas fa-comments"></i> Ulasan Pelanggan</h2>
            <p class="text-center mb-4">Lihat apa yang dikatakan pelanggan lain tentang layanan kami</p>

            <?php if (!empty($feedbacks)): ?>
                <div class="feedbacks-grid">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="feedback-card">
                            <div class="feedback-header">
                                <h4 class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></h4>
                                <div class="feedback-rating">
                                    <?php
                                    for ($i = 0; $i < 5; $i++) {
                                        if ($i < $feedback['rating']) {
                                            echo '<i class="fas fa-star star-filled"></i>';
                                        } else {
                                            echo '<i class="far fa-star star-empty"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="feedback-message">
                                <?= nl2br(htmlspecialchars($feedback['message'])) ?>
                            </div>

                            <div class="feedback-meta">
                                <span class="feedback-user">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($feedback['username'] ?? 'Anonim') ?>
                                </span>
                                <span class="feedback-date">
                                    <i class="far fa-clock"></i> <?= date('d M Y H:i', strtotime($feedback['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center py-5">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>Belum ada ulasan</h5>
                    <p>Jadilah yang pertama memberikan ulasan tentang layanan kami!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Memuat penutup HTML/BODY dan skrip JavaScript
require_once '../includes/footer.php';
?>