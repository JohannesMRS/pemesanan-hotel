<?php
require_once '../config/database.php';
$page_title = "Kontak";
require_once '../includes/header.php';

$conn = getConnection();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $rating = intval($_POST['rating']);
    
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : NULL;
    
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $subject, $message, $rating);
    
    if ($stmt->execute()) {
        $feedback_success = true;
    } else {
        $feedback_error = "Gagal mengirim feedback. Silakan coba lagi.";
    }
    $stmt->close();
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-envelope"></i> Kontak Kami</h1>
        <p>Hubungi kami untuk pertanyaan atau saran</p>
    </div>
    
    <div class="contact-container">
        <!-- Contact Info -->
        <div class="contact-info">
            <div class="info-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Alamat Kantor</h3>
                <p>Jl. Danau Toba No. 123<br>Medan, Sumatera Utara<br>Indonesia 20154</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-phone"></i>
                <h3>Telepon</h3>
                <p>(061)