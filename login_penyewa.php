<?php
require_once __DIR__ . '/koneksi.php';

if (!empty($_SESSION['penyewa_id'])) {
    redirect('portal_penyewa.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM penyewa WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $penyewa = $stmt->fetch();

    if ($penyewa && !empty($penyewa['password']) && password_verify($password, $penyewa['password'])) {
        session_regenerate_id(true);
        $_SESSION['penyewa_id']   = (int) $penyewa['id'];
        $_SESSION['penyewa_nama'] = $penyewa['nama'];
        redirect('portal_penyewa.php');
    }

    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Penyewa — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="tagline">Portal Penyewa</div>

        <div class="demo-hint">
            <strong>Akun demo</strong>
            Email <code>gojay@demo.test</code> · Password <code>penyewa123</code>
        </div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php else: render_flash(); endif; ?>

        <form method="POST" action="login_penyewa.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <p class="text-muted mt-16" style="font-size:13px;">
            Belum punya akun? <a href="daftar.php">Daftar di sini</a>
        </p>
        <p class="text-muted" style="font-size:13px;">
            <a href="lupa_password.php?tipe=penyewa">Lupa email / password?</a>
        </p>
        <p class="text-muted" style="font-size:12px;">
            <a href="login.php">Login sebagai pengelola kost</a>
        </p>
        <p class="text-muted" style="font-size:12px;">
            <a href="index.php">Kembali ke Beranda</a>
        </p>
    </div>
</div>

</body></html>
