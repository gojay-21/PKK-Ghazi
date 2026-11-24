<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM tagihan WHERE id = ?')->execute([$id]);
    flash('success', 'Tagihan berhasil dihapus.');
    redirect('tagihan.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tagihan.php');
}

$id            = (int) ($_POST['id'] ?? 0);
$penyewaId     = (int) ($_POST['penyewa_id'] ?? 0);
$jumlah        = (float) ($_POST['jumlah_tagihan'] ?? 0);
$periodeBulan  = (int) ($_POST['periode_bulan'] ?? 0);
$periodeTahun  = (int) ($_POST['periode_tahun'] ?? 0);
$jatuhTempo    = trim($_POST['tanggal_jatuh_tempo'] ?? '');
$status        = ($_POST['status'] ?? 'belum_lunas') === 'lunas' ? 'lunas' : 'belum_lunas';
$keterangan    = trim($_POST['keterangan'] ?? '');

if ($penyewaId <= 0 || $jumlah <= 0 || $periodeBulan < 1 || $periodeBulan > 12 || $periodeTahun < 2000 || $jatuhTempo === '') {
    flash('danger', 'Lengkapi data tagihan dengan benar.');
    redirect($action === 'update' ? 'tagihan.php?edit=' . $id : 'tagihan.php');
}

if ($action === 'update' && $id > 0) {
    $stmt = $pdo->prepare('UPDATE tagihan SET penyewa_id = ?, jumlah_tagihan = ?, periode_bulan = ?, periode_tahun = ?, tanggal_jatuh_tempo = ?, status = ?, keterangan = ? WHERE id = ?');
    $stmt->execute([$penyewaId, $jumlah, $periodeBulan, $periodeTahun, $jatuhTempo, $status, $keterangan, $id]);
    flash('success', 'Tagihan berhasil diperbarui.');
} else {
    $stmt = $pdo->prepare('INSERT INTO tagihan (penyewa_id, jumlah_tagihan, periode_bulan, periode_tahun, tanggal_jatuh_tempo, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$penyewaId, $jumlah, $periodeBulan, $periodeTahun, $jatuhTempo, $status, $keterangan]);
    flash('success', 'Tagihan baru berhasil dibuat.');
}

redirect('tagihan.php');
