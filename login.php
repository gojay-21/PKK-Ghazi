<?php
require_once __DIR__ . '/koneksi.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int) $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama_lengkap'];
        redirect('dashboard.php');
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="tagline">Sistem Informasi Manajemen Kost</div>

        <div class="demo-hint">
            <strong>Akun demo</strong>
            Username <code>admin</code> · Password <code>admin123</code>
        </div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php else: render_flash(); endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
        <p class="text-muted mt-16" style="font-size:13px;">
            <a href="lupa_password.php?tipe=admin">Lupa password?</a>
        </p>
        <p class="text-muted" style="font-size:12px;">
            <a href="login_penyewa.php">Login sebagai penyewa</a>
        </p>
        <p class="text-muted" style="font-size:12px;">
            <a href="index.php">Kembali ke Beranda</a>
        </p>
    </div>
</div>

</body></html>
