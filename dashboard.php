<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Dashboard';
$active     = 'dashboard';

$totalKamar    = (int) $pdo->query('SELECT COUNT(*) FROM kamar')->fetchColumn();
$kamarTerisi   = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status = 'terisi'")->fetchColumn();
$kamarKosong   = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status = 'kosong'")->fetchColumn();
$penyewaAktif  = (int) $pdo->query("SELECT COUNT(*) FROM penyewa WHERE status = 'aktif'")->fetchColumn();
$tagihanBelum  = (int) $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'belum_lunas'")->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(jumlah_bayar), 0) FROM pembayaran WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?');
$stmt->execute([(int) date('n'), (int) date('Y')]);
$pendapatan = (float) $stmt->fetchColumn();

$reminders = $pdo->query("
    SELECT t.*, p.nama
    FROM tagihan t
    JOIN penyewa p ON p.id = t.penyewa_id
    WHERE t.status = 'belum_lunas'
    ORDER BY t.tanggal_jatuh_tempo ASC, t.id ASC
    LIMIT 5
")->fetchAll();

$hariIni = strtotime(date('Y-m-d'));

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Dashboard</h1>
        <p class="text-muted mb-0">Ringkasan kondisi kost saat ini</p>
    </div>
    <div class="user-chip">Halo, <?= e($_SESSION['admin_nama']) ?></div>
</div>

<?php render_flash(); ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Total Kamar</div>
        <div class="value"><?= $totalKamar ?></div>
    </div>
    <div class="stat-card success">
        <div class="label">Kamar Terisi</div>
        <div class="value"><?= $kamarTerisi ?></div>
    </div>
    <div class="stat-card accent">
        <div class="label">Kamar Kosong</div>
        <div class="value"><?= $kamarKosong ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Penyewa Aktif</div>
        <div class="value"><?= $penyewaAktif ?></div>
    </div>
    <div class="stat-card danger">
        <div class="label">Tagihan Belum Lunas</div>
        <div class="value"><?= $tagihanBelum ?></div>
    </div>
    <div class="stat-card success">
        <div class="label">Pendapatan Bulan Ini</div>
        <div class="value"><?= rupiah($pendapatan) ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Tagihan Terdekat / Mendekati Jatuh Tempo</h3>
        <a href="pengingat.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>

    <?php if (empty($reminders)): ?>
        <div class="empty-state">Tidak ada tagihan yang perlu ditagih saat ini.</div>
    <?php else: ?>
        <?php foreach ($reminders as $r): ?>
            <?php
            $selisih = (int) floor((strtotime($r['tanggal_jatuh_tempo']) - $hariIni) / 86400);
            if ($selisih < 0) {
                $badgeClass = 'badge-danger';
                $badgeText  = 'Lewat Jatuh Tempo';
                $itemClass  = 'overdue';
            } elseif ($selisih <= 7) {
                $badgeClass = 'badge-warning';
                $badgeText  = $selisih === 0 ? 'Jatuh Tempo Hari Ini' : $selisih . ' Hari Lagi';
                $itemClass  = '';
            } else {
                $badgeClass = 'badge-neutral';
                $badgeText  = 'Akan Datang';
                $itemClass  = '';
            }
            ?>
            <div class="reminder-item <?= $itemClass ?>">
                <div class="info">
                    <strong><?= e($r['nama']) ?></strong>
                    <span>Periode <?= sprintf('%02d', $r['periode_bulan']) ?>/<?= (int) $r['periode_tahun'] ?> — Jatuh tempo: <?= tanggal_id($r['tanggal_jatuh_tempo']) ?></span>
                </div>
                <div>
                    <span class="badge <?= $badgeClass ?>">
                        <?= e($badgeText) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
