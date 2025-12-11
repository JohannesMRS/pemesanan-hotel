<?php
// admin/user/edit.php
session_start();
require_once(__DIR__ . '/../../config/database.php');

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $new_password = $_POST['new_password'];

    // Validasi
    if (empty($username) || empty($email)) {
        $_SESSION['message'] = "Username dan email wajib diisi!";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit();
    }

    // Check if username or email already exists (except current user)
    $check_sql = "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "Username atau email sudah digunakan oleh pengguna lain!";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit();
    }

    // Update user
    $sql = "UPDATE users SET username = '$username', email = '$email' WHERE id = $user_id";

    // Update password if provided
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $_SESSION['message'] = "Password minimal 6 karakter!";
            $_SESSION['message_type'] = "danger";
            header('Location: index.php');
            exit();
        }
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = '$username', email = '$email', password = '$hashed_password' WHERE id = $user_id";
    }

    if ($conn->query($sql)) {
        $_SESSION['message'] = "Data pengguna berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal memperbarui data pengguna: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }

    $conn->close();
    header('Location: index.php');
    exit();
}
