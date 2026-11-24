<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_penyewa.php';

$stmt = $pdo->prepare('
    SELECT p.*, k.nomor_kamar, k.tipe, k.harga_bulanan
    FROM penyewa p
    LEFT JOIN kamar k ON k.id = p.kamar_id
    WHERE p.id = ?
');
$stmt->execute([(int) $_SESSION['penyewa_id']]);
$penyewa = $stmt->fetch();

if (!$penyewa) {
    unset($_SESSION['penyewa_id'], $_SESSION['penyewa_nama']);
    redirect('login_penyewa.php');
}

$stmt = $pdo->prepare('SELECT * FROM tagihan WHERE penyewa_id = ? ORDER BY periode_tahun DESC, periode_bulan DESC, id ASC');
$stmt->execute([(int) $penyewa['id']]);
$daftarTagihan = $stmt->fetchAll();

$stmt = $pdo->prepare('
    SELECT b.*, t.periode_bulan, t.periode_tahun
    FROM pembayaran b
    JOIN tagihan t ON t.id = b.tagihan_id
    WHERE t.penyewa_id = ?
    ORDER BY b.tanggal_bayar DESC, b.id ASC
');
$stmt->execute([(int) $penyewa['id']]);
$riwayat = $stmt->fetchAll();

$labelMetode = [
    'tunai'    => 'Tunai',
    'transfer' => 'Transfer',
    'qris'     => 'QRIS',
    'lainnya'  => 'Lainnya',
];
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Penyewa — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div style="max-width:900px; margin:0 auto; padding:32px 20px;">

    <div class="topbar">
        <div>
            <h1>Halo, <?= e($penyewa['nama']) ?></h1>
            <p class="text-muted mb-0">Portal Penyewa KelolaKost</p>
        </div>
        <a href="logout_penyewa.php" class="btn btn-outline btn-sm">Keluar</a>
    </div>

    <?php render_flash(); ?>

    <div class="card">
        <h3>Data Kamar Anda</h3>
        <table>
            <tbody>
                <tr><td class="text-muted">Nomor Kamar</td><td><strong><?= e($penyewa['nomor_kamar'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Tipe</td><td><?= e($penyewa['tipe'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Harga per Bulan</td><td><?= rupiah($penyewa['harga_bulanan'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Tanggal Masuk</td><td><?= tanggal_id($penyewa['tanggal_masuk']) ?></td></tr>
                <tr>
                    <td class="text-muted">Email Penagihan</td>
                    <td>
                        <?php $emailTagih = email_penagihan($penyewa); ?>
                        <?= e($emailTagih !== '' ? $emailTagih : '-') ?>
                        <?php if (empty($penyewa['email_penagihan'])): ?>
                            <span class="text-muted" style="font-size:12px;">(ikut email akun)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Tagihan Anda</h3>
        <?php if (empty($daftarTagihan)): ?>
            <div class="empty-state">Belum ada tagihan untuk Anda.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Periode</th><th>Jumlah</th><th>Jatuh Tempo</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($daftarTagihan as $t): ?>
                        <?php $lunas = $t['status'] === 'lunas'; ?>
                        <tr>
                            <td><?= bulan_nama((int) $t['periode_bulan']) . ' ' . (int) $t['periode_tahun'] ?></td>
                            <td><?= rupiah($t['jumlah_tagihan']) ?></td>
                            <td><?= tanggal_id($t['tanggal_jatuh_tempo']) ?></td>
                            <td>
                                <span class="badge <?= $lunas ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $lunas ? 'Lunas' : 'Belum Lunas' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$lunas): ?>
                                    <a href="bayar_qris.php?tagihan_id=<?= (int) $t['id'] ?>" class="btn btn-primary btn-sm">Bayar QRIS</a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Riwayat Pembayaran</h3>
        <?php if (empty($riwayat)): ?>
            <div class="empty-state">Belum ada riwayat pembayaran.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Periode</th><th>Tanggal Bayar</th><th>Jumlah</th><th>Metode</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($riwayat as $b): ?>
                        <tr>
                            <td><?= bulan_nama((int) $b['periode_bulan']) . ' ' . (int) $b['periode_tahun'] ?></td>
                            <td><?= tanggal_id($b['tanggal_bayar']) ?></td>
                            <td><?= rupiah($b['jumlah_bayar']) ?></td>
                            <td>
                                <span class="badge badge-neutral"><?= e($labelMetode[$b['metode_bayar']] ?? ucfirst($b['metode_bayar'])) ?></span>
                                <?php if (!empty($b['kode_referensi'])): ?>
                                    <div class="text-muted" style="font-size:11px; margin-top:2px;"><?= e($b['kode_referensi']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><a href="bukti_pembayaran.php?id=<?= (int) $b['id'] ?>" class="btn btn-outline btn-sm">Lihat / Cetak Bukti</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="text-muted" style="font-size:12px; margin-top:8px;">
                Bukti pembayaran QRIS dapat dilihat dan dicetak/disimpan sebagai PDF melalui tombol di atas.
            </p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Ubah Email / Password Akun</h3>
        <p class="text-muted" style="font-size:13px;">Email saat ini: <strong><?= e($penyewa['email']) ?></strong></p>
        <form method="POST" action="ubah_profil_penyewa.php">
            <label for="email_baru">Email Baru</label>
            <input type="email" id="email_baru" name="email_baru" required value="<?= e($penyewa['email']) ?>">

            <label for="password_baru">Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" id="password_baru" name="password_baru" minlength="6">

            <label for="konfirmasi_password_baru">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password_baru" name="konfirmasi_password_baru" minlength="6">

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

</div>

</body></html>
