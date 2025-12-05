<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    $errors = [];
    
    if (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }
    
    // Cek username dan email sudah ada
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $errors[] = "Username atau email sudah terdaftar";
    }
    $check_stmt->close();
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            header('Location: login.php?success=Registrasi berhasil! Silakan login.');
            exit();
        } else {
            header('Location: register.php?error=Gagal mendaftar. Silakan coba lagi.');
            exit();
        }
        $stmt->close();
    } else {
        header('Location: register.php?error=' . urlencode(implode(', ', $errors)));
        exit();
    }
    
    $conn->close();
} else {
    header('Location: register.php');
    exit();
}
?>