<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT nomor_kamar FROM kamar WHERE id = ?');
    $stmt->execute([$id]);
    $kamar = $stmt->fetch();

    if (!$kamar) {
        flash('danger', 'Data kamar tidak ditemukan.');
        redirect('kamar.php');
    }

    $cek = $pdo->prepare('SELECT COUNT(*) FROM penyewa WHERE kamar_id = ?');
    $cek->execute([$id]);

    if ((int) $cek->fetchColumn() > 0) {
        flash('danger', 'Kamar ' . $kamar['nomor_kamar'] . ' tidak bisa dihapus karena masih terhubung dengan data penyewa.');
        redirect('kamar.php');
    }

    $pdo->prepare('DELETE FROM kamar WHERE id = ?')->execute([$id]);
    flash('success', 'Kamar ' . $kamar['nomor_kamar'] . ' berhasil dihapus.');
    redirect('kamar.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('kamar.php');
}

$id          = (int) ($_POST['id'] ?? 0);
$nomorKamar  = trim($_POST['nomor_kamar'] ?? '');
$tipe        = trim($_POST['tipe'] ?? '');
$harga       = (float) ($_POST['harga_bulanan'] ?? 0);
$status      = ($_POST['status'] ?? 'kosong') === 'terisi' ? 'terisi' : 'kosong';
$fasilitas   = trim($_POST['fasilitas'] ?? '');

if ($nomorKamar === '' || $tipe === '') {
    flash('danger', 'Nomor kamar dan tipe kamar wajib diisi.');
    redirect($action === 'update' ? 'kamar.php?edit=' . $id : 'kamar.php');
}

try {
    if ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE kamar SET nomor_kamar = ?, tipe = ?, harga_bulanan = ?, fasilitas = ?, status = ? WHERE id = ?');
        $stmt->execute([$nomorKamar, $tipe, $harga, $fasilitas, $status, $id]);
        flash('success', 'Data kamar berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO kamar (nomor_kamar, tipe, harga_bulanan, fasilitas, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nomorKamar, $tipe, $harga, $fasilitas, $status]);
        flash('success', 'Kamar baru berhasil ditambahkan.');
    }
} catch (PDOException $e) {
    flash('danger', 'Gagal menyimpan kamar. Pastikan nomor kamar belum dipakai.');
}

redirect('kamar.php');
