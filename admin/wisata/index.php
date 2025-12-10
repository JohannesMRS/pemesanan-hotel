<?php
// File: admin/wisata/index.php
$page_title = "Kelola Wisata";
require_once '../includes/headerAdmin.php';
?>
<div class="card">
    <h2><i class="fas fa-map-marked-alt"></i> Daftar Destinasi Wisata</h2>
    <p>Ini adalah halaman untuk mengelola (Tambah, Edit, Hapus) item di tabel **danau_toba_tours**.</p>
    <a href="add.php" style="display: inline-block; padding: 10px 15px; background: #2980b9; color: white; text-decoration: none; border-radius: 4px;">+ Tambah Wisata Baru</a>
    <p style="font-weight: bold; color: #e74c3c; margin-top: 20px;">TODO: Implementasi Tabel dan CRUD Wisata.</p>
</div>
<?php
require_once '../includes/footer.php';
?>