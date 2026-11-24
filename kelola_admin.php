<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Kelola Admin';
$active     = 'kelola_admin';

$daftarAdmin = $pdo->query('SELECT * FROM admin ORDER BY id ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Kelola Akun Admin</h1>
        <p class="text-muted mb-0">Tambah atau hapus akun pengelola kost lainnya</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Tambah Admin Baru</h3>
    </div>
    <form method="POST" action="kelola_admin_proses.php">
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div>
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
        </div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">
        <button type="submit" class="btn btn-primary">Tambah Admin</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Daftar Admin</h3>
    </div>
    <table>
        <thead><tr><th>Nama</th><th>Username</th><th>Terdaftar Sejak</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach ($daftarAdmin as $a): ?>
                <?php $akunSendiri = (int) $a['id'] === (int) $_SESSION['admin_id']; ?>
                <tr>
                    <td><strong><?= e($a['nama_lengkap']) ?></strong></td>
                    <td><?= e($a['username']) ?></td>
                    <td><?= tanggal_id($a['created_at']) ?></td>
                    <td class="actions-cell">
                        <a href="reset_password_admin.php?id=<?= (int) $a['id'] ?>" class="btn btn-outline btn-sm">Reset Password</a>
                        <?php if ($akunSendiri): ?>
                            <span class="badge badge-neutral">Akun Anda</span>
                        <?php else: ?>
                            <a href="kelola_admin_proses.php?action=delete&id=<?= (int) $a['id'] ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm="Hapus akun admin <?= e($a['nama_lengkap']) ?>?">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
