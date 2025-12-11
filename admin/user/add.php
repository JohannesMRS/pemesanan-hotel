<?php
// admin/user/add.php

// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database
require_once(__DIR__ . '/../../config/database.php');

// Fungsi cek login
if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

// Cek login dan role admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Akses ditolak!";
    $_SESSION['message_type'] = "danger";
    header('Location: ../../index.php');
    exit();
}

// Cek jika form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getConnection();

    // 1. Ambil dan validasi input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // Default role 'user'

    // Validasi dasar
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['message'] = "Semua field bertanda bintang (*) harus diisi!";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit();
    }

    // Validasi panjang password
    if (strlen($password) < 6) {
        $_SESSION['message'] = "Password minimal 6 karakter!";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit();
    }

    // 2. Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 3. Cek apakah username atau email sudah terdaftar (gunakan Prepared Statement)
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($check_sql);

        if (!$stmt_check) {
            throw new Exception("Prepare statement check failed: " . $conn->error);
        }

        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $_SESSION['message'] = "Username atau Email sudah terdaftar!";
            $_SESSION['message_type'] = "warning";
            $stmt_check->close();
            header('Location: index.php');
            exit();
        }
        $stmt_check->close();

        // 4. Masukkan data ke database (gunakan Prepared Statement)
        $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);

        if (!$stmt_insert) {
            throw new Exception("Prepare statement insert failed: " . $conn->error);
        }

        $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt_insert->execute()) {
            $_SESSION['message'] = "Pengguna **" . htmlspecialchars($username) . "** berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menambahkan pengguna! Error: " . $stmt_insert->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt_insert->close();
    } catch (Exception $e) {
        $_SESSION['message'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    $conn->close();
    header('Location: index.php');
    exit();
} else {
    // Jika diakses tidak melalui POST, arahkan kembali
    header('Location: index.php');
    exit();
}
