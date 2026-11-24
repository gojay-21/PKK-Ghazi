<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Reset Password Admin';
$active     = 'kelola_admin';

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM admin WHERE id = ?');
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    flash('danger', 'Akun admin tidak ditemukan.');
    redirect('kelola_admin.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password   = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
        $update->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        flash('success', 'Password admin ' . $admin['nama_lengkap'] . ' berhasil diubah.');
        redirect('kelola_admin.php');
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Reset Password Admin</h1>
        <p class="text-muted mb-0">Atur ulang password untuk akun: <strong><?= e($admin['nama_lengkap']) ?></strong> (<?= e($admin['username']) ?>)</p>
    </div>
</div>

<?php render_flash(); ?>
<?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px;">
    <form method="POST" action="reset_password_admin.php">
        <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">

        <label for="password_baru">Password Baru</label>
        <input type="password" id="password_baru" name="password_baru" required minlength="6" autofocus>

        <label for="konfirmasi_password">Konfirmasi Password Baru</label>
        <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">

        <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
        <a href="kelola_admin.php" class="btn btn-outline">Batal</a>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
