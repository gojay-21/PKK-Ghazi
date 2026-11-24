<?php
require_once __DIR__ . '/koneksi.php';

$tipe = $_POST['tipe'] ?? $_GET['tipe'] ?? 'admin';
if (!in_array($tipe, ['admin', 'penyewa'], true)) {
    $tipe = 'admin';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password    = $_POST['password_baru'] ?? '';
    $konfirmasi  = $_POST['konfirmasi_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif ($tipe === 'admin') {
        $username    = trim($_POST['username'] ?? '');
        $namaLengkap = trim($_POST['nama_lengkap'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || strcasecmp($admin['nama_lengkap'], $namaLengkap) !== 0) {
            $error = 'Username dan nama lengkap tidak cocok.';
        } else {
            $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
            flash('success', 'Password berhasil diubah. Silakan login.');
            redirect('login.php');
        }
    } else {
        $penyewaId = (int) ($_POST['penyewa_id'] ?? 0);
        $noHp      = trim($_POST['no_hp'] ?? '');
        $emailBaru = trim($_POST['email_baru'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM penyewa WHERE id = ?');
        $stmt->execute([$penyewaId]);
        $penyewa = $stmt->fetch();

        $hpCocok = $penyewa && preg_replace('/\D+/', '', (string) $penyewa['no_hp']) === preg_replace('/\D+/', '', $noHp);

        if (!$penyewa || !$hpCocok) {
            $error = 'Nama dan No. HP tidak cocok dengan data pengelola kost.';
        } elseif (!filter_var($emailBaru, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            $cek = $pdo->prepare('SELECT COUNT(*) FROM penyewa WHERE email = ? AND id <> ?');
            $cek->execute([$emailBaru, $penyewaId]);
            if ((int) $cek->fetchColumn() > 0) {
                $error = 'Email sudah dipakai akun lain.';
            } else {
                $update = $pdo->prepare('UPDATE penyewa SET email = ?, password = ? WHERE id = ?');
                $update->execute([$emailBaru, password_hash($password, PASSWORD_DEFAULT), $penyewaId]);
                flash('success', 'Email dan password berhasil diubah. Silakan login.');
                redirect('login_penyewa.php');
            }
        }
    }
}

$daftarPenyewa = $pdo->query('SELECT id, nama FROM penyewa ORDER BY nama ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card" style="max-width:420px;">
        <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="tagline">Lupa Password</div>

        <div class="tab-switch">
            <a href="lupa_password.php?tipe=penyewa" class="<?= $tipe === 'penyewa' ? 'active' : '' ?>">Penyewa</a>
            <a href="lupa_password.php?tipe=admin" class="<?= $tipe === 'admin' ? 'active' : '' ?>">Pengelola</a>
        </div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($tipe === 'admin'): ?>
        <form method="POST" action="lupa_password.php">
            <input type="hidden" name="tipe" value="admin">
            <p class="text-muted" style="font-size:12px;">Masukkan username dan nama lengkap akun Anda untuk verifikasi.</p>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required>

            <label for="password_baru">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" required minlength="6">

            <label for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">

            <button type="submit" class="btn btn-primary btn-block">Ubah Password</button>
        </form>
        <?php else: ?>
        <form method="POST" action="lupa_password.php">
            <input type="hidden" name="tipe" value="penyewa">
            <p class="text-muted" style="font-size:12px;">Masukkan nama dan No. HP sesuai data di pengelola kost untuk verifikasi.</p>

            <label for="penyewa_id">Nama Anda</label>
            <select id="penyewa_id" name="penyewa_id" required>
                <option value="">Pilih Nama</option>
                <?php foreach ($daftarPenyewa as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= e($p['nama']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="no_hp">No. HP (sesuai data di pengelola)</label>
            <input type="text" id="no_hp" name="no_hp" required>

            <label for="email_baru">Email Baru (boleh sama dengan yang lama)</label>
            <input type="email" id="email_baru" name="email_baru" required>

            <label for="password_baru">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" required minlength="6">

            <label for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">

            <button type="submit" class="btn btn-primary btn-block">Ubah Email &amp; Password</button>
        </form>
        <?php endif; ?>

        <p class="text-muted mt-16" style="font-size:12px;">
            <a href="<?= $tipe === 'admin' ? 'login.php' : 'login_penyewa.php' ?>">← Kembali ke Login</a>
        </p>
    </div>
</div>

</body></html>
