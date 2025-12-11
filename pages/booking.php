<?php
// // Pastikan sesi dimulai jika belum ada di header.php
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

require_once '../config/database.php';
require_once '../includes/header.php';

// Pastikan pengguna sudah login.
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Mengambil ID pengguna yang sudah login
$user_id = $_SESSION['id'] ?? null;

$conn = getConnection();
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;

// Variabel untuk menampung data form dan pesan
$check_in = '';
$check_out = '';
$guests = 1;
$error_message = '';
$success_message = '';
$hotel = null;
$booking_id_for_pdf = null; // Deklarasi variabel untuk ID Booking yang baru

// =======================================================
// TAMBAHAN: 1. Ambil Data User (Nama dan Email)
// =======================================================
$user_name = '';
$user_email = '';

$stmt_user = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

if ($user_data) {
    $user_name = $user_data['username'];
    $user_email = $user_data['email'];
}
$stmt_user->close();
// =======================================================

// 1. Ambil data Hotel berdasarkan hotel_id
if ($hotel_id > 0) {
    $stmt_hotel = $conn->prepare("SELECT name, price_per_night FROM hotels WHERE id = ?");
    $stmt_hotel->bind_param("i", $hotel_id);
    $stmt_hotel->execute();
    $result_hotel = $stmt_hotel->get_result();
    $hotel = $result_hotel->fetch_assoc();
    $stmt_hotel->close();

    if (!$hotel) {
        $error_message = "Hotel tidak ditemukan.";
    }
} else {
    $error_message = "ID Hotel tidak valid.";
}

// 2. Tangani Proses Pemesanan (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hotel) {
    // Ambil dan bersihkan input
    $check_in = trim($_POST['check_in']);
    $check_out = trim($_POST['check_out']);
    $guests = intval($_POST['guests']);

    // Ambil hotel_id dari POST (sudah ada di GET tapi lebih aman di POST)
    $posted_hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;

    // Validasi Dasar
    if (empty($check_in) || empty($check_out) || $guests < 1) {
        $error_message = "Semua field (Tanggal Check-in, Check-out, dan Jumlah Tamu) harus diisi dengan benar.";
    } elseif (strtotime($check_in) >= strtotime($check_out)) {
        $error_message = "Tanggal Check-out harus setelah Tanggal Check-in.";
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d', strtotime('-1 day')))) {
        $error_message = "Tanggal Check-in tidak boleh di masa lalu.";
    } else {
        // Hitung total harga
        $date1 = new DateTime($check_in);
        $date2 = new DateTime($check_out);
        $interval = $date1->diff($date2);
        $num_nights = $interval->days;

        if ($num_nights < 1) {
            $error_message = "Pemesanan minimal 1 malam.";
        } else {
            $price_per_night = $hotel['price_per_night'];
            $total_price = $price_per_night * $num_nights;

            // Status default: 'pending'
            $status = 'pending';

            // Konversi total_price ke string agar sesuai dengan tipe DECIMAL(10,2) di database
            $total_price_str = number_format($total_price, 2, '.', '');


            // Masukkan data ke tabel bookings
            // CATATAN: Saya asumsikan kolom user ID di tabel bookings adalah 'user_id', bukan 'id'.
            // Namun, karena kode Anda menggunakan 'id' di INSERT, saya sesuaikan dengan yang Anda kirim.
            $query = "INSERT INTO bookings (hotel_id, id, check_in, check_out, guests, total_price, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("iississ", $hotel_id, $user_id, $check_in, $check_out, $guests, $total_price_str, $status);

            if ($stmt->execute()) {
                // 1. Ambil ID Booking yang baru saja di-generate
                $id_booking_baru = $conn->insert_id;

                // ASUMSI: Setiap pemesanan ini adalah 1 kamar
                $jumlah_kamar = 1;
                // Subtotal di sini sama dengan total_price yang sudah dihitung
                $subtotal_detail = $total_price_str;

                // 2. Insert ke tabel detail_pesanan
                $query_detail = "INSERT INTO detail_pesanan (id_booking, jumlah_kamar, Subtotal) 
                                 VALUES (?, ?, ?)";

                $stmt_detail = $conn->prepare($query_detail);
                // Tipe binding: integer (id_booking), integer (jumlah_kamar), string (Subtotal)
                $stmt_detail->bind_param("iis", $id_booking_baru, $jumlah_kamar, $subtotal_detail);

                if ($stmt_detail->execute()) {
                    // Berhasil menyimpan ke bookings DAN detail_pesanan
                    $success_message = "Pemesanan berhasil dibuat! Anda sekarang dapat mencetak bukti pemesanan.";
                    $booking_id_for_pdf = $id_booking_baru; // Simpan ID untuk link PDF
                } else {
                    // Jika detail_pesanan gagal, anggap pemesanan gagal (atau catat error)
                    $error_message = "Pemesanan berhasil, tetapi gagal menyimpan detail pesanan. Error: " . $stmt_detail->error;
                }
                $stmt_detail->close();

                // Reset form setelah berhasil
                $check_in = $check_out = '';
                $guests = 1;
            } else {
                $error_message = "Gagal menyimpan pemesanan. Coba lagi. Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> Form Pemesanan Hotel</h1>
        <?php if ($hotel): ?>
            <p>Anda sedang memesan: <strong><?php echo htmlspecialchars($hotel['name']); ?></strong></p>
        <?php endif; ?>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>

        <?php if (isset($booking_id_for_pdf)): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../includes/generate_pdf.php?id=<?php echo $booking_id_for_pdf; ?>"
                    class="btn-pdf"
                    target="_blank"
                    style="display: inline-block; padding: 12px 25px; background-color: #e74c3c; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 1.1rem; transition: background-color 0.3s;">
                    <i class="fas fa-file-pdf"></i> Download Bukti Pemesanan (PDF)
                </a>
            </div>
        <?php endif; ?>

        <a href="hotels.php" class="btn btn-primary" style="display: block; text-align: center; margin-top: 15px;">Kembali ke Daftar Hotel</a>
    <?php endif; ?>

    <?php if ($hotel && !$success_message): ?>
        <div class="booking-card">
            <form method="POST" action="booking.php?hotel_id=<?php echo $hotel_id; ?>">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">

                <div class="form-group">
                    <label for="user_name">Nama Pemesan:</label>
                    <input type="text" id="user_name" name="user_name"
                        value="<?php echo htmlspecialchars($user_name); ?>"
                        readonly style="background-color: #f0f0f0; cursor: not-allowed; opacity: 0.8;">
                </div>

                <div class="form-group">
                    <label for="user_email">Email Kontak:</label>
                    <input type="email" id="user_email" name="user_email"
                        value="<?php echo htmlspecialchars($user_email); ?>"
                        readonly style="background-color: #f0f0f0; cursor: not-allowed; opacity: 0.8;">
                </div>
                <div class="form-group">
                    <label for="check_in">Tanggal Check-in:</label>
                    <input type="date" id="check_in" name="check_in"
                        value="<?php echo htmlspecialchars($check_in); ?>"
                        min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="check_out">Tanggal Check-out:</label>
                    <input type="date" id="check_out" name="check_out"
                        value="<?php echo htmlspecialchars($check_out); ?>"
                        min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="guests">Jumlah Kamar:</label>
                    <input type="number" id="guests" name="guests" min="1" max="10"
                        value="<?php echo htmlspecialchars($guests); ?>" required>
                </div>

                <p class="price-info">
                    Harga per malam: <strong>Rp <?php echo number_format($hotel['price_per_night'], 0, ',', '.'); ?></strong>
                </p>

                <div class="button-group">
                    <button type="submit" class="btn-submit"><i class="fas fa-credit-card"></i> Konfirmasi Pesanan</button>
                    <a href="hotels.php" class="btn-cancel"><i class="fas fa-times-circle"></i> Batal</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
    /* ------------------------------------------- */
    /* PERBAIKAN STYLING JUDUL DAN KARTU */
    /* ------------------------------------------- */
    .page-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 30px 20px;
        background: #ffffff;
        /* Ubah latar belakang menjadi putih solid */
        border-radius: 10px;
        border-bottom: 3px solid #2ecc71;
        /* Garis pemisah hijau */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .page-header h1 {
        color: #34495e;
        margin-top: 0;
        font-size: 2.5rem;
    }

    .page-header p {
        color: #7f8c8d;
        font-size: 1.1rem;
        margin-top: 10px;
    }


    .booking-card {
        max-width: 500px;
        margin: 0 auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        /* Bayangan lebih kuat */
        border: 1px solid #e0e0e0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #34495e;
    }

    .form-group input[type="date"],
    .form-group input[type="number"],
    .form-group input[type="text"],
    /* Ditambahkan */
    .form-group input[type="email"]

    /* Ditambahkan */
        {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        border-color: #2ecc71;
        outline: none;
    }

    .price-info {
        text-align: center;
        margin-top: 25px;
        font-size: 1.1rem;
        color: #7f8c8d;
    }

    /* ------------------------------------------- */
    /* STYLING TOMBOL BARU */
    /* ------------------------------------------- */
    .button-group {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-submit {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
    }

    .btn-cancel {
        display: block;
        width: 100%;
        text-align: center;
        padding: 15px;
        text-decoration: none;
        background-color: #e74c3c;
        /* Merah Solid */
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
    }

    .btn-cancel:hover {
        background-color: #c0392b;
        /* Merah Lebih Gelap saat hover */
        transform: translateY(-1px);
    }

    /* ------------------------------------------- */
    /* STYLING ALERT */
    /* ------------------------------------------- */

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        font-weight: 600;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* STYLING TOMBOL KEMBALI DI BAGIAN SUKSES */
    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }
</style>

<?php
// Tutup koneksi
if ($conn) $conn->close();

require_once '../includes/footer.php';
?>