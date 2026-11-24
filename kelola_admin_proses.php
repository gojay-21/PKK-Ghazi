<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id === (int) $_SESSION['admin_id']) {
        flash('danger', 'Anda tidak bisa menghapus akun sendiri.');
        redirect('kelola_admin.php');
    }

    $total = (int) $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn();
    if ($total <= 1) {
        flash('danger', 'Minimal harus ada satu akun admin.');
        redirect('kelola_admin.php');
    }

    $stmt = $pdo->prepare('SELECT nama_lengkap FROM admin WHERE id = ?');
    $stmt->execute([$id]);
    $nama = $stmt->fetchColumn();

    if ($nama === false) {
        flash('danger', 'Akun admin tidak ditemukan.');
        redirect('kelola_admin.php');
    }

    $pdo->prepare('DELETE FROM admin WHERE id = ?')->execute([$id]);
    flash('success', 'Akun admin ' . $nama . ' berhasil dihapus.');
    redirect('kelola_admin.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('kelola_admin.php');
}

$namaLengkap = trim($_POST['nama_lengkap'] ?? '');
$username    = trim($_POST['username'] ?? '');
$password    = $_POST['password'] ?? '';

if ($namaLengkap === '' || $username === '' || strlen($password) < 6) {
    flash('danger', 'Nama, username, dan password minimal 6 karakter wajib diisi.');
    redirect('kelola_admin.php');
}

$cek = $pdo->prepare('SELECT COUNT(*) FROM admin WHERE username = ?');
$cek->execute([$username]);
if ((int) $cek->fetchColumn() > 0) {
    flash('danger', 'Username sudah dipakai admin lain.');
    redirect('kelola_admin.php');
}

$stmt = $pdo->prepare('INSERT INTO admin (nama_lengkap, username, password) VALUES (?, ?, ?)');
$stmt->execute([$namaLengkap, $username, password_hash($password, PASSWORD_DEFAULT)]);

flash('success', 'Admin baru berhasil ditambahkan.');
redirect('kelola_admin.php');
