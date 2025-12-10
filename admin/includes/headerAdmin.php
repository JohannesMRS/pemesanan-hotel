<?php
// File: admin/includes/admin_header.php
// Jalur mundur DUA KALI untuk mencapai includes/ dan config/
require_once '../config/database.php';
// Menggunakan auth_sheck.php dari folder includes di root
require_once '../includes/auth_sheck.php';

// Pengecekan keamanan: wajib admin untuk setiap halaman
requireAdmin();

$conn = getConnection();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Dasar Admin */
        body {
            font-family: sans-serif;
            background-color: #f4f7f6;
            margin: 0;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: 100%;
            box-sizing: border-box;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            color: white;
            transition: transform 0.3s;
            text-decoration: none;
            display: block;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php require_once 'sidebarAdmin.php'; // Sidebar 
        ?>
        <div class="main-content">