<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pengaturan.php');
}

$namaKost      = trim($_POST['nama_kost'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');
$deskripsi     = trim($_POST['deskripsi'] ?? '');
$kontakWa      = trim($_POST['kontak_wa'] ?? '');
$kontakEmail   = trim($_POST['kontak_email'] ?? '');
$jamOperasional = trim($_POST['jam_operasional'] ?? '');

if ($namaKost === '') {
    flash('danger', 'Nama kost wajib diisi.');
    redirect('pengaturan.php');
}

$stmt = $pdo->prepare('UPDATE pengaturan SET nama_kost = ?, alamat = ?, deskripsi = ?, kontak_wa = ?, kontak_email = ?, jam_operasional = ? WHERE id = 1');
$stmt->execute([$namaKost, $alamat, $deskripsi, $kontakWa, $kontakEmail, $jamOperasional]);

flash('success', 'Pengaturan kost berhasil disimpan.');
redirect('pengaturan.php');
