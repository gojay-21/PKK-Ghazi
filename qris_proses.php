<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_penyewa.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('portal_penyewa.php');
}

$tagihanId = (int) ($_POST['tagihan_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM tagihan WHERE id = ? AND penyewa_id = ?');
$stmt->execute([$tagihanId, (int) $_SESSION['penyewa_id']]);
$tagihan = $stmt->fetch();

if (!$tagihan) {
    flash('danger', 'Tagihan tidak ditemukan.');
    redirect('portal_penyewa.php');
}

if ($tagihan['status'] === 'lunas') {
    flash('warning', 'Tagihan ini sudah lunas.');
    redirect('portal_penyewa.php');
}

$tanggal = date('Y-m-d');
$kode    = buat_kode_referensi($tanggal);

$stmt = $pdo->prepare('INSERT INTO pembayaran (tagihan_id, jumlah_bayar, tanggal_bayar, metode_bayar, catatan, kode_referensi) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $tagihanId,
    $tagihan['jumlah_tagihan'],
    $tanggal,
    'qris',
    'Pembayaran via QRIS (simulasi/uji coba)',
    $kode,
]);

$pembayaranId = (int) $pdo->lastInsertId();

$pdo->prepare("UPDATE tagihan SET status = 'lunas' WHERE id = ?")->execute([$tagihanId]);

flash('success', 'Pembayaran QRIS berhasil dicatat.');
redirect('bukti_pembayaran.php?id=' . $pembayaranId);
