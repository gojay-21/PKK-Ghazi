<?php
require_once __DIR__ . '/koneksi.php';

if (!empty($_SESSION['penyewa_id'])) {
    redirect('portal_penyewa.php');
}

$error = '';

// Hanya penyewa yang belum punya akun (email kosong) yang bisa didaftarkan.
$calon = $pdo->query("SELECT id, nama FROM penyewa WHERE email IS NULL OR email = '' ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $penyewaId = (int) ($_POST['penyewa_id'] ?? 0);
    $noHp      = trim($_POST['no_hp'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM penyewa WHERE id = ?');
    $stmt->execute([$penyewaId]);
    $penyewa = $stmt->fetch();

    $hpCocok = $penyewa && preg_replace('/\D+/', '', (string) $penyewa['no_hp']) === preg_replace('/\D+/', '', $noHp);

    if (!$penyewa) {
        $error = 'Nama penyewa tidak ditemukan. Hubungi pengelola kost.';
    } elseif (!empty($penyewa['email'])) {
        $error = 'Penyewa ini sudah memiliki akun. Silakan login atau gunakan lupa password.';
    } elseif (!$hpCocok) {
        $error = 'No. HP tidak sesuai dengan data di pengelola kost.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM penyewa WHERE email = ? AND id <> ?');
        $cek->execute([$email, $penyewaId]);
        if ((int) $cek->fetchColumn() > 0) {
            $error = 'Email sudah dipakai akun lain.';
        } else {
            $update = $pdo->prepare('UPDATE penyewa SET email = ?, password = ? WHERE id = ?');
            $update->execute([$email, password_hash($password, PASSWORD_DEFAULT), $penyewaId]);
            flash('success', 'Akun berhasil dibuat. Silakan login dengan email dan password Anda.');
            redirect('login_penyewa.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Penyewa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card" style="max-width:420px;">
        <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="tagline">Daftar Akun Penyewa</div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="daftar.php">
            <label for="penyewa_id">Nama Anda</label>
            <select id="penyewa_id" name="penyewa_id" required>
                <option value="">Pilih Nama</option>
                <?php foreach ($calon as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"<?= (int) ($_POST['penyewa_id'] ?? 0) === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['nama']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="text-muted" style="font-size:12px; margin-top:-12px;">
                Tidak ada nama Anda? Hubungi pengelola kost untuk didaftarkan sebagai penyewa terlebih dahulu.
            </p>

            <label for="no_hp">No. HP (sesuai data di pengelola)</label>
            <input type="text" id="no_hp" name="no_hp" required value="<?= e($_POST['no_hp'] ?? '') ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="6">

            <label for="konfirmasi_password">Konfirmasi Password</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">

            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>

        <p class="text-muted mt-16" style="font-size:13px;">
            Sudah punya akun? <a href="login_penyewa.php">Login di sini</a>
        </p>
    </div>
</div>

</body></html>
