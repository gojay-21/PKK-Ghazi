<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT * FROM penyewa WHERE id = ?');
    $stmt->execute([$id]);
    $penyewa = $stmt->fetch();

    if (!$penyewa) {
        flash('danger', 'Data penyewa tidak ditemukan.');
        redirect('penyewa.php');
    }

    $pdo->prepare('DELETE FROM penyewa WHERE id = ?')->execute([$id]);
    sync_kamar_status($pdo, (int) $penyewa['kamar_id']);

    flash('success', 'Data penyewa ' . $penyewa['nama'] . ' berhasil dihapus.');
    redirect('penyewa.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('penyewa.php');
}

$id           = (int) ($_POST['id'] ?? 0);
$nama         = trim($_POST['nama'] ?? '');
$kamarId      = (int) ($_POST['kamar_id'] ?? 0);
$noHp         = trim($_POST['no_hp'] ?? '');
$email        = trim($_POST['email'] ?? '');
$emailPenagihan = trim($_POST['email_penagihan'] ?? '');
$noKtp        = trim($_POST['no_ktp'] ?? '');
$tanggalMasuk = trim($_POST['tanggal_masuk'] ?? '');
$alamatAsal   = trim($_POST['alamat_asal'] ?? '');
$status       = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

if ($nama === '' || $kamarId <= 0 || $tanggalMasuk === '') {
    flash('danger', 'Nama, kamar, dan tanggal masuk wajib diisi.');
    redirect($action === 'update' ? 'penyewa.php?edit=' . $id : 'penyewa.php');
}

if ($emailPenagihan !== '' && !filter_var($emailPenagihan, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'Format email penagihan tidak valid.');
    redirect($action === 'update' ? 'penyewa.php?edit=' . $id : 'penyewa.php');
}

$stmt = $pdo->prepare('SELECT id FROM kamar WHERE id = ?');
$stmt->execute([$kamarId]);
if (!$stmt->fetchColumn()) {
    flash('danger', 'Kamar yang dipilih tidak ditemukan.');
    redirect($action === 'update' ? 'penyewa.php?edit=' . $id : 'penyewa.php');
}

$emailDb          = $email === '' ? null : $email;
$emailPenagihanDb = $emailPenagihan === '' ? null : $emailPenagihan;
$kamarLama = 0;

try {
    if ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare('SELECT kamar_id FROM penyewa WHERE id = ?');
        $stmt->execute([$id]);
        $kamarLama = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare('UPDATE penyewa SET nama = ?, kamar_id = ?, no_hp = ?, email = ?, email_penagihan = ?, no_ktp = ?, tanggal_masuk = ?, alamat_asal = ?, status = ? WHERE id = ?');
        $stmt->execute([$nama, $kamarId, $noHp, $emailDb, $emailPenagihanDb, $noKtp, $tanggalMasuk, $alamatAsal, $status, $id]);
        flash('success', 'Data penyewa berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO penyewa (nama, kamar_id, no_hp, email, email_penagihan, no_ktp, tanggal_masuk, alamat_asal, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nama, $kamarId, $noHp, $emailDb, $emailPenagihanDb, $noKtp, $tanggalMasuk, $alamatAsal, $status]);
        flash('success', 'Penyewa baru berhasil ditambahkan.');
    }

    sync_kamar_status($pdo, $kamarLama);
    sync_kamar_status($pdo, $kamarId);
} catch (PDOException $e) {
    flash('danger', 'Gagal menyimpan data penyewa. Pastikan email belum dipakai penyewa lain.');
}

redirect('penyewa.php');
