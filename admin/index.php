<?php
// File: admin/index.php (DI ROOT ADMIN)
// Jalur mundur sekali ke includes/ di root
require_once '../includes/auth_sheck.php';

requireAdmin();

// Jika lolos, arahkan ke dashboard
header("Location: dashboard.php");
exit;
