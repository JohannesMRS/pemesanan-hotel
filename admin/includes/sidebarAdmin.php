<?php
// Cek session
// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database
require_once '../config/database.php';

if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Dapatkan nama file saat ini
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<aside class="sidebar" style="width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto; z-index: 1000; transition: left 0.3s ease;">

    <!-- Sidebar Header -->
    <div class="sidebar-header text-center py-4" style="border-bottom: 1px solid #34495e;">
        <div class="user-avatar mb-3">
            <div style="width: 70px; height: 70px; background: #3498db; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
        <h5 class="mb-1" style="color: #ecf0f1;">
            <?php
            echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
            ?>
        </h5>
        <small class="text-muted" style="color: #bdc3c7 !important;">
            <i class="fas fa-user-shield me-1"></i>Administrator
        </small>
    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu mt-3" style="list-style: none; padding: 0;">
        <?php
        // Definisikan menu items
        $menu_items = [
            [
                'label' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'url' => '../dashboard.php',
                'active' => ($current_file == 'dashboard.php')
            ],
            [
                'label' => 'Kelola Pengguna',
                'icon' => 'fas fa-users',
                'url' => '../user/index.php',
                'active' => ($current_dir == 'user')
            ],
            [
                'label' => 'Kelola Hotel',
                'icon' => 'fas fa-hotel',
                'url' => '../hotels/index.php',
                'active' => ($current_dir == 'hotels')
            ],
            [
                'label' => 'Kelola Pesanan',
                'icon' => 'fas fa-list-alt',
                'url' => '../bookings/index.php',
                'active' => ($current_dir == 'bookings')
            ],
        ];

        foreach ($menu_items as $item):
            $is_active = $item['active'];
            $active_style = $is_active ?
                'background: #34495e; border-left: 4px solid #2ecc71; color: #2ecc71 !important;' :
                '';
        ?>
            <li class="sidebar-item" style="margin-bottom: 2px;">
                <a href="<?php echo $item['url']; ?>"
                    class="sidebar-link d-flex align-items-center"
                    style="padding: 12px 20px; color: #bdc3c7; text-decoration: none; transition: all 0.3s; <?php echo $active_style; ?>">
                    <i class="<?php echo $item['icon']; ?>" style="width: 20px; margin-right: 12px; text-align: center;"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>

        <!-- Separator -->
        <li style="margin: 25px 20px; border-top: 1px solid #34495e;"></li>

        <!-- Logout -->
        <li class="sidebar-item" style="position: absolute; bottom: 0; width: 100%;">
            <a href="../../auth/logout.php"
                class="sidebar-link d-flex align-items-center"
                style="padding: 15px 20px; background: #c0392b; color: white !important; text-decoration: none; border-top: 1px solid rgba(255,255,255,0.1);"
                onclick="return confirm('Yakin ingin keluar?')">
                <i class="fas fa-sign-out-alt" style="width: 20px; margin-right: 12px; text-align: center;"></i>
                <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>

<style>
    .sidebar-link:hover {
        background: #34495e !important;
        color: #2ecc71 !important;
    }

    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
        }
    }
</style>