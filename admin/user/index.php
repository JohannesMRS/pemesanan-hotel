<?php
// admin/user/index.php
// START: Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Anda tidak memiliki akses ke halaman ini!";
    $_SESSION['message_type'] = "danger";
    header('Location: ../../index.php');
    exit();
}

$conn = getConnection();
$pageTitle = "Kelola Pengguna";

// Handle delete - Telah diperbaiki untuk menangani Foreign Key menggunakan Prepared Statements dan Transaksi
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];

    // Jangan izinkan hapus diri sendiri (gunakan 'user_id' untuk konsistensi sesi)
    if ($delete_id != $_SESSION['user_id']) {
        try {
            // 1. NONAKTIFKAN autocommit dan mulai transaksi
            $conn->begin_transaction();

            // 2. HAPUS DATA ANAK (CHILD ROWS) dari tabel 'bookings' terlebih dahulu
            // PERBAIKAN PENTING: Foreign Key di tabel 'bookings' merujuk ke ID pengguna,
            // harusnya menggunakan kolom 'user_id', BUKAN 'id' dari tabel bookings.
            $delete_bookings_sql = "DELETE FROM bookings WHERE user_id = ?";
            $stmt_bookings = $conn->prepare($delete_bookings_sql);

            if (!$stmt_bookings) {
                throw new Exception("Prepare statement bookings failed: " . $conn->error);
            }

            $stmt_bookings->bind_param("i", $delete_id);
            $stmt_bookings->execute();
            $stmt_bookings->close();

            // 3. HAPUS DATA INDUK (PARENT ROW) dari tabel 'users'
            $delete_users_sql = "DELETE FROM users WHERE id = ?";
            $stmt_users = $conn->prepare($delete_users_sql);

            if (!$stmt_users) {
                throw new Exception("Prepare statement users failed: " . $conn->error);
            }

            $stmt_users->bind_param("i", $delete_id);

            if ($stmt_users->execute()) {
                // 4. COMMIT transaksi jika kedua operasi berhasil
                $conn->commit();
                $_SESSION['message'] = "Pengguna dan data pemesanan terkait berhasil dihapus!";
                $_SESSION['message_type'] = "success";
            } else {
                // ROLLBACK jika eksekusi penghapusan users gagal
                $conn->rollback();
                $_SESSION['message'] = "Gagal menghapus pengguna (Operasi Hapus Users Gagal)!";
                $_SESSION['message_type'] = "danger";
            }
            $stmt_users->close();
        } catch (Exception $e) {
            // Tangani error database atau prepared statement, pastikan rollback
            if ($conn && $conn->in_transaction) { // Perubahan: menggunakan in_transaction
                $conn->rollback();
            }
            // Tangkap dan tampilkan pesan error yang lebih detail
            $_SESSION['message'] = "Gagal menghapus pengguna: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }

        header('Location: index.php');
        exit();
    } else {
        $_SESSION['message'] = "Tidak dapat menghapus akun sendiri!";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit();
    }
}
// Hapus semua kode PHP untuk 'Handle role change' di sini.

// Get all users
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Include admin header
require_once(__DIR__ . '/../includes/headerAdmin.php');
require_once(__DIR__ . '/sidebarAdmin.php');
?>

<div class="main-content" id="mainContent" style="margin-left: 250px; padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kelola Pengguna</h1>
            <p class="text-muted mb-0">Ini adalah halaman untuk melihat, mengedit (jika ada fitur edit), dan menghapus akun pengguna.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
        </button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show mb-4" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body p-0">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>USERNAME</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th>TANGGAL BUAT</th>
                                <th style="width: 10%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counter = 1;
                            while ($row = $result->fetch_assoc()):
                                // PERBAIKAN PENTING: Gunakan $_SESSION['user_id'] di sini, bukan $_SESSION['id']
                                $is_current_user = ($row['id'] == $_SESSION['id']);
                            ?>
                                <tr>
                                    <td class="text-muted"><?php echo $counter++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3" style="width: 40px; height: 40px; background: #3498db; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong class="d-block"><?php echo htmlspecialchars($row['username']); ?></strong>
                                                <?php if ($is_current_user): ?>
                                                    <small class="text-primary">(Anda)</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($row['role'] == 'admin' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="far fa-calendar me-1 text-muted"></i>
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal<?php echo $row['id']; ?>"
                                                title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Yakin ingin menghapus pengguna ini? Semua data pemesanannya juga akan terhapus!');"
                                                title="Hapus"
                                                <?php echo $is_current_user ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editUserModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="edit.php">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Pengguna</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Username</label>
                                                        <input type="text" class="form-control" name="username"
                                                            value="<?php echo htmlspecialchars($row['username']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" class="form-control" name="email"
                                                            value="<?php echo htmlspecialchars($row['email']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Password Baru</label>
                                                        <input type="password" class="form-control" name="new_password" placeholder="Kosongkan jika tidak ingin mengubah">
                                                        <small class="form-text text-muted">Minimal 6 karakter</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4>Tidak ada data pengguna</h4>
                    <p class="text-muted">Belum ada pengguna yang terdaftar dalam sistem.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        Total <?php echo $result->num_rows; ?> pengguna terdaftar
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        © <?php echo date('Y'); ?> Danau Toba Ticketing System - Admin Panel v1.0
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="add.php">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required placeholder="contoh: cristine">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="contoh: cristine@gmail.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <small class="form-text text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambah Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once(__DIR__ . '/../includes/footer.php');
$conn->close();
?>

<style>
    /* ... Style CSS tidak berubah ... */
    .avatar-circle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-top: 1px solid #dee2e6;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
    }

    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.35rem;
    }

    .btn-group .btn {
        border-radius: 5px !important;
        margin: 0 2px;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-outline-warning {
        color: #ffc107;
        border-color: #ffc107;
    }

    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
</style>