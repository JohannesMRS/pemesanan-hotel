<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
requireLogin();
requireAdmin();

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Cek ada parameter id
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID booking tidak valid']);
    exit();
}

$booking_id = intval($_POST['id']);
$conn = getConnection();

// Cek apakah booking ada
$check_sql = "SELECT id_booking FROM bookings WHERE id_booking = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
    exit();
}

// Delete booking
$delete_sql = "DELETE FROM bookings WHERE id_booking = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $booking_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dihapus',
        'id' => $booking_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
