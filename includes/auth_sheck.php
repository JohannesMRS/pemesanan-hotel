<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi Pengecekan Admin
function requireAdmin()
{
    // Asumsi role admin disimpan di $_SESSION['role']
    if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
        // Redirect ke halaman login dari folder admin
        header("Location: ../auth/login.php");
        exit;
    }
}

// Asumsi getBaseUrl() ada di config/constants.php
require_once __DIR__ . '/../config/constants.php';
