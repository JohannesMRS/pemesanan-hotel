<?php require_once '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Danau Toba Ticketing</title>
    <link rel="stylesheet" href="../assets/style/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                <p>Masuk ke akun Anda untuk memesan hotel</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form action="proses_login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username atau Email</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Masukkan username atau email">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Masukkan password Anda">
                </div>
                
                <button type="submit" class="btn-auth">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
                <p><a href="../pages/home.php"><i class="fas fa-home"></i> Kembali ke Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>