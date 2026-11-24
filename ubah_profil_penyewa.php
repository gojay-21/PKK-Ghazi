<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_penyewa.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('portal_penyewa.php');
}

$emailBaru  = trim($_POST['email_baru'] ?? '');
$password   = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi_password_baru'] ?? '';
$id         = (int) $_SESSION['penyewa_id'];

if (!filter_var($emailBaru, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'Format email tidak valid.');
    redirect('portal_penyewa.php');
}

$cek = $pdo->prepare('SELECT COUNT(*) FROM penyewa WHERE email = ? AND id <> ?');
$cek->execute([$emailBaru, $id]);
if ((int) $cek->fetchColumn() > 0) {
    flash('danger', 'Email sudah dipakai akun lain.');
    redirect('portal_penyewa.php');
}

if ($password !== '' || $konfirmasi !== '') {
    if (strlen($password) < 6) {
        flash('danger', 'Password baru minimal 6 karakter.');
        redirect('portal_penyewa.php');
    }
    if ($password !== $konfirmasi) {
        flash('danger', 'Konfirmasi password baru tidak sama.');
        redirect('portal_penyewa.php');
    }
    $stmt = $pdo->prepare('UPDATE penyewa SET email = ?, password = ? WHERE id = ?');
    $stmt->execute([$emailBaru, password_hash($password, PASSWORD_DEFAULT), $id]);
    flash('success', 'Email dan password berhasil diperbarui.');
} else {
    $stmt = $pdo->prepare('UPDATE penyewa SET email = ? WHERE id = ?');
    $stmt->execute([$emailBaru, $id]);
    flash('success', 'Email berhasil diperbarui.');
}

redirect('portal_penyewa.php');
