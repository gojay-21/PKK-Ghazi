<?php
require_once __DIR__ . '/koneksi.php';

$isAdmin   = !empty($_SESSION['admin_id']);
$isPenyewa = !empty($_SESSION['penyewa_id']);

if (!$isAdmin && !$isPenyewa) {
    redirect('login_penyewa.php');
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT b.*, t.periode_bulan, t.periode_tahun, t.jumlah_tagihan,
           p.id AS penyewa_id, p.nama, p.email, p.email_penagihan,
           k.nomor_kamar, k.tipe
    FROM pembayaran b
    JOIN tagihan t ON t.id = b.tagihan_id
    JOIN penyewa p ON p.id = t.penyewa_id
    LEFT JOIN kamar k ON k.id = p.kamar_id
    WHERE b.id = ?
');
$stmt->execute([$id]);
$b = $stmt->fetch();

if (!$b) {
    flash('danger', 'Data pembayaran tidak ditemukan.');
    redirect($isAdmin ? 'pembayaran.php' : 'portal_penyewa.php');
}

if ($isPenyewa && (int) $b['penyewa_id'] !== (int) $_SESSION['penyewa_id']) {
    flash('danger', 'Anda tidak berhak melihat bukti pembayaran ini.');
    redirect('portal_penyewa.php');
}

$labelMetode = [
    'tunai'    => 'Tunai',
    'transfer' => 'Transfer',
    'qris'     => 'QRIS',
    'lainnya'  => 'Lainnya',
];

$kodeReferensi = $b['kode_referensi'];
if (empty($kodeReferensi)) {
    $prefix = $b['metode_bayar'] === 'qris' ? 'QRIS' : 'BYR';
    $kodeReferensi = $prefix . '-' . date('Ymd', strtotime($b['tanggal_bayar'])) . '-' . strtoupper(substr(md5('pembayaran-' . $b['id']), 0, 6));
}
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $b['metode_bayar'] === 'qris' ? 'Bukti Pembayaran QRIS' : 'Bukti Pembayaran' ?> — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="bukti-page">
    <div class="bukti-card" id="bukti-cetak">
        <div class="bukti-header">
            <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
            <div class="bukti-status">✔ PEMBAYARAN BERHASIL</div>
        </div>

        <div class="bukti-jumlah">
            <?= rupiah($b['jumlah_bayar']) ?>
        </div>

        <table class="bukti-table">
            <tbody>
                <tr><td class="text-muted">No. Referensi</td><td><strong><?= e($kodeReferensi) ?></strong></td></tr>
                <tr><td class="text-muted">Nama Penyewa</td><td><?= e($b['nama']) ?></td></tr>
                <tr><td class="text-muted">Email Penagihan</td><td><?= e(email_penagihan($b) ?: '-') ?></td></tr>
                <tr><td class="text-muted">Kamar</td><td><?= e($b['nomor_kamar'] ?? '-') ?> (<?= e($b['tipe'] ?? '-') ?>)</td></tr>
                <tr><td class="text-muted">Periode Tagihan</td><td><?= bulan_nama((int) $b['periode_bulan']) . ' ' . (int) $b['periode_tahun'] ?></td></tr>
                <tr><td class="text-muted">Tanggal Bayar</td><td><?= tanggal_id($b['tanggal_bayar']) ?></td></tr>
                <tr>
                    <td class="text-muted">Metode Pembayaran</td>
                    <td>
                        <span class="badge badge-neutral"><?= e($labelMetode[$b['metode_bayar']] ?? ucfirst($b['metode_bayar'])) ?></span>
                        <?php if ($b['metode_bayar'] === 'qris'): ?>
                            <span class="text-muted" style="font-size:12px;">(simulasi)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($b['catatan'])): ?>
                    <tr><td class="text-muted">Catatan</td><td><?= e($b['catatan']) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p class="bukti-footer-note">
            Bukti ini dibuat otomatis oleh sistem KelolaKost pada <?= tanggal_jam_id($b['created_at']) ?>.
            <?php if ($b['metode_bayar'] === 'qris'): ?>
                <br><em>Pembayaran QRIS pada sistem ini adalah simulasi untuk keperluan uji coba.</em>
            <?php endif; ?>
        </p>
    </div>

    <div class="bukti-actions">
        <button onclick="window.print()" class="btn btn-primary">Cetak / Simpan PDF</button>
        <a href="<?= $isAdmin ? 'pembayaran.php' : 'portal_penyewa.php' ?>" class="btn btn-outline">Kembali</a>
    </div>
</div>

</body></html>
