<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT tagihan_id FROM pembayaran WHERE id = ?');
    $stmt->execute([$id]);
    $tagihanId = $stmt->fetchColumn();

    if ($tagihanId === false) {
        flash('danger', 'Data pembayaran tidak ditemukan.');
        redirect('pembayaran.php');
    }

    $pdo->prepare('DELETE FROM pembayaran WHERE id = ?')->execute([$id]);
    sync_status_tagihan($pdo, (int) $tagihanId);

    flash('success', 'Riwayat pembayaran berhasil dihapus. Tagihan kembali berstatus belum lunas.');
    redirect('pembayaran.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pembayaran.php');
}

$tagihanId   = (int) ($_POST['tagihan_id'] ?? 0);
$jumlahBayar = (float) ($_POST['jumlah_bayar'] ?? 0);
$tanggalBayar = trim($_POST['tanggal_bayar'] ?? '');
$metode      = $_POST['metode_bayar'] ?? 'tunai';
$catatan     = trim($_POST['catatan'] ?? '');

if (!in_array($metode, ['tunai', 'transfer', 'qris', 'lainnya'], true)) {
    $metode = 'tunai';
}

if ($tagihanId <= 0 || $jumlahBayar <= 0 || $tanggalBayar === '') {
    flash('danger', 'Lengkapi data pembayaran dengan benar.');
    redirect('pembayaran.php');
}

$stmt = $pdo->prepare('SELECT id FROM tagihan WHERE id = ?');
$stmt->execute([$tagihanId]);
if (!$stmt->fetchColumn()) {
    flash('danger', 'Tagihan yang dipilih tidak ditemukan.');
    redirect('pembayaran.php');
}

$kode = $metode === 'qris' ? buat_kode_referensi($tanggalBayar) : 'BYR-' . date('Ymd', strtotime($tanggalBayar)) . '-' . strtoupper(bin2hex(random_bytes(3)));

$stmt = $pdo->prepare('INSERT INTO pembayaran (tagihan_id, jumlah_bayar, tanggal_bayar, metode_bayar, catatan, kode_referensi) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([$tagihanId, $jumlahBayar, $tanggalBayar, $metode, $catatan, $kode]);

sync_status_tagihan($pdo, $tagihanId);

flash('success', 'Pembayaran berhasil dicatat.');
redirect('pembayaran.php');
