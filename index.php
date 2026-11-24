<?php
require_once __DIR__ . '/koneksi.php';

$set       = pengaturan($pdo);
$totalKamar = (int) $pdo->query('SELECT COUNT(*) FROM kamar')->fetchColumn();
$kamarKosong = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status = 'kosong'")->fetchColumn();

$daftarKamar = $pdo->query("
    SELECT * FROM kamar
    ORDER BY (status = 'terisi') ASC, nomor_kamar ASC
")->fetchAll();

$wa = preg_replace('/\D+/', '', (string) $set['kontak_wa']);
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($set['nama_kost']) ?> — Info Kost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="landing-hero">
    <div class="landing-nav">
        <div class="brand" style="color:#fff;">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="landing-nav-links">
            <a href="login_penyewa.php">Login Penyewa</a>
            <a href="login.php">Login Pengelola</a>
        </div>
    </div>

    <div class="landing-hero-content">
        <h1><?= e($set['nama_kost']) ?></h1>
        <p class="landing-alamat">📍 <?= e($set['alamat']) ?></p>
        <?php if (!empty($set['deskripsi'])): ?>
            <p class="landing-desc"><?= e($set['deskripsi']) ?></p>
        <?php endif; ?>

        <div class="landing-quick-stats">
            <div><strong><?= $totalKamar ?></strong><span>Total Kamar</span></div>
            <div><strong><?= $kamarKosong ?></strong><span>Kamar Kosong</span></div>
        </div>

        <div class="landing-cta">
            <?php if ($wa !== ''): ?>
                <a href="https://wa.me/<?= e($wa) ?>" target="_blank" class="btn btn-accent">Tanya via WhatsApp</a>
            <?php endif; ?>
            <a href="#kamar" class="btn btn-outline-light">Lihat Kamar Tersedia</a>
        </div>
    </div>
</header>

<main class="landing-main">
    <section id="kamar">
        <h2>Daftar Kamar</h2>
        <p class="text-muted">Berikut kondisi kamar kost saat ini secara real-time.</p>

        <div class="kamar-grid">
            <?php foreach ($daftarKamar as $k): ?>
                <?php $tersedia = $k['status'] === 'kosong'; ?>
                <div class="kamar-card <?= $tersedia ? 'tersedia' : 'terisi' ?>">
                    <div class="kamar-card-head">
                        <h3>Kamar <?= e($k['nomor_kamar']) ?></h3>
                        <span class="badge <?= $tersedia ? 'badge-success' : 'badge-neutral' ?>">
                            <?= $tersedia ? 'Tersedia' : 'Terisi' ?>
                        </span>
                    </div>
                    <div class="kamar-card-tipe"><?= e($k['tipe']) ?></div>
                    <div class="kamar-card-harga"><?= rupiah($k['harga_bulanan']) ?> <span>/ bulan</span></div>
                    <?php if (!empty($k['fasilitas'])): ?>
                        <div class="kamar-card-fasilitas"><?= e($k['fasilitas']) ?></div>
                    <?php endif; ?>
                    <?php if ($tersedia && $wa !== ''): ?>
                        <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Halo, saya tertarik dengan Kamar ' . $k['nomor_kamar'] . ' di ' . $set['nama_kost']) ?>" target="_blank" class="btn btn-primary btn-sm btn-block">Hubungi untuk Sewa</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="landing-info-grid">
        <div class="card">
            <h3>Informasi Kost</h3>
            <table>
                <tbody>
                    <tr><td class="text-muted">Alamat</td><td><?= e($set['alamat']) ?></td></tr>
                    <tr><td class="text-muted">Jam Operasional</td><td><?= e($set['jam_operasional']) ?></td></tr>
                    <?php if (!empty($set['kontak_wa'])): ?>
                        <tr><td class="text-muted">WhatsApp</td><td><?= e($set['kontak_wa']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($set['kontak_email'])): ?>
                        <tr><td class="text-muted">Email</td><td><?= e($set['kontak_email']) ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Sudah Jadi Penghuni?</h3>
            <p class="text-muted">Masuk ke portal penyewa untuk melihat tagihan, riwayat pembayaran, dan bayar sewa langsung via QRIS.</p>
            <a href="login_penyewa.php" class="btn btn-primary btn-block">Login Penyewa</a>
            <p class="text-muted mt-16" style="font-size:13px; text-align:center;">
                Belum punya akun? <a href="daftar.php">Daftar di sini</a>
            </p>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <p>© <?= date('Y') ?> <?= e($set['nama_kost']) ?>. Dikelola dengan <?= e($set['nama_kost']) ?>.</p>
    <p><a href="login.php">Login Pengelola Kost</a></p>
</footer>

</body></html>
