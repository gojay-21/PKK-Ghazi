<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Pengingat Jatuh Tempo';
$active     = 'pengingat';
$set        = pengaturan($pdo);

$daftar = $pdo->query("
    SELECT t.*, p.nama, p.no_hp, p.email, p.email_penagihan, k.nomor_kamar
    FROM tagihan t
    JOIN penyewa p ON p.id = t.penyewa_id
    LEFT JOIN kamar k ON k.id = p.kamar_id
    WHERE t.status = 'belum_lunas'
    ORDER BY t.tanggal_jatuh_tempo ASC, t.id ASC
")->fetchAll();

$hariIni    = strtotime(date('Y-m-d'));
$terlambat  = [];
$mendekati  = [];
$akanDatang = [];

foreach ($daftar as $t) {
    $selisih = (int) floor((strtotime($t['tanggal_jatuh_tempo']) - $hariIni) / 86400);
    $t['selisih'] = $selisih;

    if ($selisih < 0) {
        $terlambat[] = $t;
    } elseif ($selisih <= 7) {
        $mendekati[] = $t;
    } else {
        $akanDatang[] = $t;
    }
}

/**
 * Render satu baris pengingat beserta tombol kirim email.
 */
function render_reminder_item(array $t, array $set, string $badgeClass, string $badgeText, string $itemClass = ''): void
{
    $emailTagih = email_penagihan($t);
    $periode    = bulan_nama((int) $t['periode_bulan']) . ' ' . (int) $t['periode_tahun'];

    $subjek = 'Pengingat Tagihan ' . $set['nama_kost'] . ' — ' . $periode;
    $isi    = "Halo " . $t['nama'] . ",\n\n"
        . 'Kami mengingatkan tagihan sewa kamar ' . ($t['nomor_kamar'] ?? '-') . ' untuk periode ' . $periode . ' sebesar ' . rupiah($t['jumlah_tagihan']) . ".\n"
        . 'Jatuh tempo: ' . tanggal_id($t['tanggal_jatuh_tempo']) . ".\n\n"
        . "Silakan melakukan pembayaran melalui portal penyewa atau hubungi pengelola kost.\n\n"
        . 'Terima kasih,' . "\n" . $set['nama_kost'];
    ?>
    <div class="reminder-item <?= e($itemClass) ?>">
        <div class="info">
            <strong><?= e($t['nama']) ?></strong>
            <span>Periode <?= e($periode) ?> — <?= rupiah($t['jumlah_tagihan']) ?> — Jatuh tempo: <?= tanggal_id($t['tanggal_jatuh_tempo']) ?> — <?= e($t['no_hp']) ?></span>
            <span class="text-muted" style="font-size:12px;">✉ <?= e($emailTagih !== '' ? $emailTagih : 'Email penagihan belum diisi') ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
            <span class="badge <?= e($badgeClass) ?>"><?= e($badgeText) ?></span>
            <?php if ($emailTagih !== ''): ?>
                <a href="<?= e(link_mailto($emailTagih, $subjek, $isi)) ?>" class="btn btn-outline btn-sm">Kirim Pengingat</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Pengingat Jatuh Tempo</h1>
        <p class="text-muted mb-0">Pantau tagihan yang perlu segera ditagih</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <h3>Sudah Lewat Jatuh Tempo (<?= count($terlambat) ?>)</h3>
    <?php if (empty($terlambat)): ?>
        <div class="empty-state">Tidak ada tagihan yang lewat jatuh tempo.</div>
    <?php else: ?>
        <?php foreach ($terlambat as $t): ?>
            <?php render_reminder_item($t, $set, 'badge-danger', 'Terlambat ' . abs($t['selisih']) . ' hari', 'overdue'); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Jatuh Tempo dalam 7 Hari (<?= count($mendekati) ?>)</h3>
    <?php if (empty($mendekati)): ?>
        <div class="empty-state">Tidak ada tagihan yang mendekati jatuh tempo.</div>
    <?php else: ?>
        <?php foreach ($mendekati as $t): ?>
            <?php render_reminder_item($t, $set, 'badge-warning', $t['selisih'] === 0 ? 'Hari ini' : $t['selisih'] . ' hari lagi'); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Akan Datang (<?= count($akanDatang) ?>)</h3>
    <?php if (empty($akanDatang)): ?>
        <div class="empty-state">Tidak ada tagihan lain yang tertunda.</div>
    <?php else: ?>
        <?php foreach ($akanDatang as $t): ?>
            <?php render_reminder_item($t, $set, 'badge-neutral', $t['selisih'] . ' hari lagi'); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
