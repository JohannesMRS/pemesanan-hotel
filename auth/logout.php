<?php
require_once '../config/database.php';

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke home
header('Location: ../index.php');
exit();
?>