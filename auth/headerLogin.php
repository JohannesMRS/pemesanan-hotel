<?php
// auth/headerAuth.php
// Pastikan session_start() sudah dipanggil di file utama (login.php / register.php)

$page_title = $page_title ?? "Aplikasi Pemesanan Hotel"; // Gunakan title yang sudah diset
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #f8f9fa; /* Warna latar belakang ringan */
            padding-top: 50px;
        }
        .card {
            border-radius: 15px; /* Sudut membulat modern */
        }
    </style>
</head>
<body>

<div class="container">
    <!-- ```

--- -->

<!-- ## 💾 Struktur Tabel MySQL

Anda memerlukan tabel `users` di database Anda (`pemesanan_hotel_db`) untuk menyimpan data otentikasi.

```sql
CREATE TABLE users (
    id_user INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL, -- Wajib 255 karena hash panjang
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Contoh pengguna admin (Password: admin123)
-- Pastikan untuk menghasilkan hash ini menggunakan PHP (password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO users (nama, email, password, role) VALUES 
('Admin Utama', 'admin@hotel.com', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'admin'); -->