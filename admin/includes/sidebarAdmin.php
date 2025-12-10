<aside class="sidebar" style="width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #ecf0f1; padding-bottom: 15px;">Admin Panel</h2>
    <ul style="list-style: none; padding: 0;">
        <?php
        // Daftar menu
        $menu_items = [
            'dashboard.php' => ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
            'user/index.php' => ['icon' => 'fas fa-users', 'label' => 'Kelola Pengguna'],
            'hotels/index.php' => ['icon' => 'fas fa-hotel', 'label' => 'Kelola Hotel'], // Masih dipertahankan
            'wisata/index.php' => ['icon' => 'fas fa-map-marked-alt', 'label' => 'Kelola Wisata'], // Modul Wisata Baru
            'bookings/index.php' => ['icon' => 'fas fa-list-alt', 'label' => 'Kelola Pesanan'],
        ];

        foreach ($menu_items as $url => $item):
            $is_active = (strpos($_SERVER['PHP_SELF'], $url) !== false) ? 'background: #34495e; border-left: 5px solid #2ecc71;' : '';
            // Tentukan jalur relatif yang benar
            $full_url = (strpos($url, '/') === false) ? $url : '../' . $url;
        ?>
            <li style="margin-bottom: 5px;">
                <a href="<?php echo $full_url; ?>" style="display: block; padding: 12px 20px; color: white; text-decoration: none; transition: background 0.3s; <?php echo $is_active; ?>">
                    <i class="<?php echo $item['icon']; ?>" style="margin-right: 10px;"></i> <?php echo $item['label']; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li style="margin-top: 30px;">
            <a href="../auth/logout.php" style="display: block; padding: 12px 20px; color: #e74c3c; text-decoration: none; border-top: 1px solid #34495e;">
                <i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i> Logout
            </a>
        </li>
    </ul>
</aside>