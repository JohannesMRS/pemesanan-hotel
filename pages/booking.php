<?php
// generate_pdf.php - Generate PDF untuk bukti booking
session_start();

// Cek apakah ada booking_id
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    die("Booking ID tidak valid");
}

$booking_id = intval($_GET['booking_id']);

// Cek apakah ada data booking di session
if (!isset($_SESSION['last_booking']) || $_SESSION['last_booking']['id'] != $booking_id) {
    die("Data booking tidak ditemukan. Silakan lakukan pemesanan ulang.");
}

$booking = $_SESSION['last_booking'];

// Format tanggal
$check_in_formatted = date('d/m/Y', strtotime($booking['check_in']));
$check_out_formatted = date('d/m/Y', strtotime($booking['check_out']));
$booking_date_formatted = date('d/m/Y H:i', strtotime($booking['booking_date']));

// Header untuk PDF atau HTML
if (isset($_GET['download']) && $_GET['download'] == 'true') {
    // Jika ingin download sebagai PDF (butuh library)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="booking_' . $booking['booking_ref'] . '.pdf"');

    // Tampilkan pesan untuk install library PDF
    echo "Untuk menghasilkan PDF, silakan install library PDF generator seperti TCPDF atau Dompdf.\n";
    echo "Atau gunakan tombol 'Cetak' untuk mencetak versi HTML.";
    exit;
}
// Jika hanya preview, tampilkan HTML dengan CSS
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Booking #<?php echo $booking_id; ?></title>
    <link rel="stylesheet" href="../assets/style/pdf.css"> <!-- CSS khusus untuk PDF -->
</head>

<body>
    <div class="pdf-container">
        <div class="pdf-header">
            <div class="pdf-title">BUKTI PEMESANAN HOTEL</div>
            <div class="pdf-subtitle">Booking Reference: <?php echo $booking['booking_ref']; ?></div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">Informasi Booking</div>
            <table class="pdf-table">
                <tr>
                    <td class="pdf-label">Booking ID</td>
                    <td>#<?php echo $booking_id; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Kode Referensi</td>
                    <td><?php echo $booking['booking_ref']; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Tanggal Booking</td>
                    <td><?php echo $booking_date_formatted; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Status</td>
                    <td><span class="pdf-status">PENDING</span></td>
                </tr>
            </table>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">Detail Hotel</div>
            <table class="pdf-table">
                <tr>
                    <td class="pdf-label">Nama Hotel</td>
                    <td><?php echo $booking['hotel_name']; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Harga per Malam</td>
                    <td>Rp <?php echo number_format($booking['hotel_price'], 0, ',', '.'); ?></td>
                </tr>
            </table>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">Detail Tamu</div>
            <table class="pdf-table">
                <tr>
                    <td class="pdf-label">Nama Lengkap</td>
                    <td><?php echo $booking['full_name']; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Email</td>
                    <td><?php echo $booking['email']; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Telepon</td>
                    <td><?php echo $booking['phone']; ?></td>
                </tr>
            </table>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">Detail Pemesanan</div>
            <table class="pdf-table">
                <tr>
                    <td class="pdf-label">Check-in</td>
                    <td><?php echo $check_in_formatted; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Check-out</td>
                    <td><?php echo $check_out_formatted; ?></td>
                </tr>
                <tr>
                    <td class="pdf-label">Jumlah Malam</td>
                    <td><?php echo $booking['nights']; ?> malam</td>
                </tr>
                <tr>
                    <td class="pdf-label">Jumlah Tamu</td>
                    <td><?php echo $booking['guests']; ?> orang</td>
                </tr>
                <tr>
                    <td class="pdf-label">Permintaan Khusus</td>
                    <td><?php echo $booking['special_requests'] ?: '-'; ?></td>
                </tr>
            </table>
        </div>

        <div class="pdf-total">
            TOTAL PEMBAYARAN: Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
        </div>

        <div class="pdf-footer">
            <p><strong>CATATAN PENTING:</strong></p>
            <p>1. Bukti ini merupakan konfirmasi reservasi awal</p>
            <p>2. Pembayaran dapat dilakukan saat check-in di hotel</p>
            <p>3. Reservasi dapat dibatalkan maksimal 24 jam sebelum check-in</p>
            <p>4. Untuk perubahan reservasi, hubungi customer service</p>
            <p>5. Dokumen ini dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="pdf-watermark">BOOKING CONFIRMATION</div>
    </div>

    <div class="pdf-actions">
        <a href="generate_pdf.php?booking_id=<?php echo $booking_id; ?>&download=true" class="pdf-btn pdf-btn-success">
            📥 Download PDF
        </a>
        <button onclick="window.print()" class="pdf-btn pdf-btn-primary">
            🖨️ Cetak
        </button>
        <a href="booking.php" class="pdf-btn pdf-btn-secondary">
            ← Kembali
        </a>
    </div>

    <script>
        // Auto print jika parameter print=true
        if (window.location.search.includes('print=true')) {
            window.print();
        }
    </script>
</body>

</html>