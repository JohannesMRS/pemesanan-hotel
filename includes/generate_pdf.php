<?php
// =================================================================
// 1. AUTOLOAD DENGAN COMPOSER
// =================================================================
// __DIR__ akan mengarah ke folder includes. Kita perlu mundur satu langkah (../) 
// untuk mencapai folder vendor di root proyek.
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Pastikan koneksi database tersedia
require_once '../config/database.php';
$conn = getConnection();

// Ambil ID Booking dari URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id === 0) {
    die("ID Pemesanan tidak valid.");
}

// =================================================================
// 2. Ambil Data Lengkap Pemesanan (Menggunakan b.user_id yang Benar)
// =================================================================

// Kueri yang benar: b.user_id harus JOIN ke u.id
$query = "SELECT 
            b.id_booking, b.check_in, b.check_out, b.guests, b.total_price, b.booking_date, b.status,
            h.name AS hotel_name, h.price_per_night,
            dp.jumlah_kamar, dp.Subtotal,
            u.username AS user_name, u.email AS user_email
          FROM bookings b
          LEFT JOIN hotels h ON b.hotel_id = h.id  -- Tetap LEFT JOIN, meskipun ini jarang kosong
          LEFT JOIN detail_pesanan dp ON b.id_booking = dp.id_booking
          LEFT JOIN users u ON b.id = u.id 
          WHERE b.id_booking = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data pemesanan tidak ditemukan.");
}

$stmt->close();
$conn->close();

// Hitung jumlah malam
$date1 = new DateTime($data['check_in']);
$date2 = new DateTime($data['check_out']);
$interval = $date1->diff($date2);
$num_nights = $interval->days;

// =================================================================
// 3. Buat HTML Konfirmasi (yang akan diubah menjadi PDF)
// =================================================================

ob_start(); // Mulai output buffering
?>
<!DOCTYPE html>
<html>

<head>
    <title>Bukti Pemesanan #<?php echo $booking_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .container {
            width: 95%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        h1 {
            text-align: center;
            color: #2ecc71;
            border-bottom: 2px solid #2ecc71;
            padding-bottom: 10px;
        }

        h3 {
            color: #34495e;
            margin-top: 20px;
            border-left: 5px solid #2ecc71;
            padding-left: 10px;
        }

        .info-box {
            padding: 10px;
            margin-bottom: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #bdc3c7;
        }

        .total {
            font-weight: bold;
            background-color: #f39c12;
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>KONFIRMASI PEMESANAN HOTEL</h1>
        <p style="text-align: center;">Nomor Pemesanan: <strong>#<?php echo $data['id_booking']; ?></strong> | Tanggal Pesan: <?php echo date('d M Y H:i', strtotime($data['booking_date'])); ?></p>

        <h3>Detail Pemesan</h3>
        <div class="info-box">
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($data['user_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($data['user_email']); ?></p>
        </div>

        <h3>Detail Hotel & Tanggal</h3>
        <div class="info-box">
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($data['hotel_name']); ?></p>
            <p><strong>Check-in:</strong> <?php echo date('d F Y', strtotime($data['check_in'])); ?></p>
            <p><strong>Check-out:</strong> <?php echo date('d F Y', strtotime($data['check_out'])); ?></p>
            <p><strong>Durasi:</strong> <?php echo $num_nights; ?> Malam</p>
            <p><strong>Jumlah Tamu:</strong> <?php echo $data['guests']; ?> Orang</p>
            <p><strong>Status Pembayaran:</strong> <span style="color: red; font-weight: bold;"><?php echo strtoupper($data['status']); ?></span></p>
        </div>

        <h3>Rincian Biaya</h3>
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Harga/Malam</th>
                    <th>Jml Malam</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Kamar (<?php echo $data['jumlah_kamar']; ?> Kamar)</td>
                    <td>Rp <?php echo number_format($data['price_per_night'], 0, ',', '.'); ?></td>
                    <td><?php echo $num_nights; ?></td>
                    <td>Rp <?php echo number_format($data['Subtotal'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="3">TOTAL YANG HARUS DIBAYAR</td>
                    <td>Rp <?php echo number_format($data['total_price'], 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Dokumen ini adalah bukti konfirmasi pemesanan. Mohon tunjukkan saat check-in.</p>
        </div>
    </div>
</body>

</html>
<?php
$html = ob_get_clean(); // Ambil semua output HTML

// =================================================================
// 4. Konfigurasi dan Render PDF menggunakan Dompdf
// =================================================================

$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

// (Opsional) Atur ukuran kertas dan orientasi
$dompdf->setPaper('A4', 'portrait');

// Render HTML menjadi PDF
$dompdf->render();

// Output PDF ke browser (force download)
$filename = "Bukti_Pemesanan_" . $booking_id . ".pdf";
$dompdf->stream($filename, array("Attachment" => 1));

exit;
?>