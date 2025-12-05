<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Danau Toba Ticketing</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header & Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-water"></i>
                        <span>Danau Toba Ticketing</span>
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages/hotels.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>">
                            <i class="fas fa-hotel"></i> Hotel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages/contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Kontak
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages/about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                            <i class="fas fa-info-circle"></i> Tentang Kami
                        </a>
                    </li>
                    
                    <!-- Auth Menu -->
                    <li class="nav-item auth-menu">
                        <?php if (isLoggedIn()): ?>
                            <div class="user-dropdown">
                                <button class="user-btn">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-content">
                                    <?php if (isAdmin()): ?>
                                        <a href="admin/index.php"><i class="fas fa-cog"></i> Admin Panel</a>
                                    <?php endif; ?>
                                    <a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="auth/register.php" class="btn-register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>