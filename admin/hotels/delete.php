<?php
// START: Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database dan fungsi helper
require_once('../../config/database.php');

// Cek login
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Cek apakah parameter 'id' ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID Hotel tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$hotel_id = (int)$_GET['id'];

// 1. Ambil nama file gambar (image_url) yang terkait sebelum menghapus record
$sql_select = "SELECT image_url FROM hotels WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $hotel_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows === 0) {
    $_SESSION['message'] = "Hotel dengan ID tersebut tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    $conn->close();
    header('Location: index.php');
    exit();
}

$row = $result_select->fetch_assoc();
$image_to_delete = $row['image_url'];
$stmt_select->close();

// 2. Hapus data hotel dari database
$sql_delete = "DELETE FROM hotels WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $hotel_id);

if ($stmt_delete->execute()) {

    // 3. Hapus file gambar dari server (jika ada)
    if (!empty($image_to_delete)) {
        // Tentukan path gambar, mundur dua langkah dari admin/hotels/ ke root lalu ke img/
        $file_path = "../../img/" . $image_to_delete;

        // Cek apakah file ada sebelum menghapus
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                // Gambar berhasil dihapus
            } else {
                // Jika gagal menghapus gambar (ini jarang terjadi kecuali masalah permission)
                // Kita tetap biarkan proses delete data berhasil.
                error_log("Gagal menghapus file gambar: " . $file_path);
            }
        }
    }

    $_SESSION['message'] = "Hotel berhasil dihapus, dan file gambar terkait (jika ada) telah dihapus dari server.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Gagal menghapus hotel: " . $conn->error;
    $_SESSION['message_type'] = "danger";
}

$stmt_delete->close();
$conn->close();

// 4. Redirect kembali ke halaman index.php
header('Location: index.php');
exit();
