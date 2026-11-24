<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Laporan Keuangan';
$active     = 'laporan';

$bulan = (int) ($_GET['bulan'] ?? date('n'));
$tahun = (int) ($_GET['tahun'] ?? date('Y'));

if ($bulan < 1 || $bulan > 12) {
    $bulan = (int) date('n');
}
if ($tahun < 2000 || $tahun > 2100) {
    $tahun = (int) date('Y');
}

$stmt = $pdo->prepare('SELECT COALESCE(SUM(jumlah_bayar), 0) FROM pembayaran WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?');
$stmt->execute([$bulan, $tahun]);
$totalDiterima = (float) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(jumlah_tagihan), 0) FROM tagihan WHERE periode_bulan = ? AND periode_tahun = ?');
$stmt->execute([$bulan, $tahun]);
$totalTagihan = (float) $stmt->fetchColumn();

$stmt = $pdo->prepare('
    SELECT COALESCE(SUM(b.jumlah_bayar), 0)
    FROM pembayaran b
    JOIN tagihan t ON t.id = b.tagihan_id
    WHERE t.periode_bulan = ? AND t.periode_tahun = ?
');
$stmt->execute([$bulan, $tahun]);
$dibayarUntukPeriode = (float) $stmt->fetchColumn();

$belumTertagih = max(0, $totalTagihan - $dibayarUntukPeriode);

$stmt = $pdo->prepare('
    SELECT b.*, t.periode_bulan, t.periode_tahun, p.nama, k.nomor_kamar
    FROM pembayaran b
    JOIN tagihan t ON t.id = b.tagihan_id
    JOIN penyewa p ON p.id = t.penyewa_id
    LEFT JOIN kamar k ON k.id = p.kamar_id
    WHERE MONTH(b.tanggal_bayar) = ? AND YEAR(b.tanggal_bayar) = ?
    ORDER BY b.tanggal_bayar ASC, b.id ASC
');
$stmt->execute([$bulan, $tahun]);
$rincian = $stmt->fetchAll();

$labelMetode = [
    'tunai'    => 'Tunai',
    'transfer' => 'Transfer',
    'qris'     => 'QRIS',
    'lainnya'  => 'Lainnya',
];

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Laporan Keuangan</h1>
        <p class="text-muted mb-0">Ringkasan pemasukan berdasarkan periode</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <form method="GET" action="laporan.php" class="d-flex gap-8 align-center" style="flex-wrap:wrap;">
        <div style="min-width:160px;">
            <label for="bulan" class="mb-0">Bulan</label>
            <select id="bulan" name="bulan">
                <?php for ($b = 1; $b <= 12; $b++): ?>
                    <option value="<?= $b ?>"<?= $bulan === $b ? ' selected' : '' ?>><?= bulan_nama($b) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="min-width:120px;">
            <label for="tahun" class="mb-0">Tahun</label>
            <input type="number" id="tahun" name="tahun" value="<?= $tahun ?>">
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="margin-top:22px;">Tampilkan</button>
        </div>
    </form>
</div>

<div class="stat-grid">
    <div class="stat-card success">
        <div class="label">Total Diterima — <?= bulan_nama($bulan) . ' ' . $tahun ?></div>
        <div class="value"><?= rupiah($totalDiterima) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Total Tagihan Periode Ini</div>
        <div class="value"><?= rupiah($totalTagihan) ?></div>
    </div>
    <div class="stat-card danger">
        <div class="label">Belum Tertagih</div>
        <div class="value"><?= rupiah($belumTertagih) ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Rincian Pemasukan — <?= bulan_nama($bulan) . ' ' . $tahun ?></h3>
    </div>

    <?php if (empty($rincian)): ?>
        <div class="empty-state">Belum ada pemasukan pada periode ini.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Penyewa</th>
                    <th>Kamar</th>
                    <th>Tanggal Bayar</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rincian as $r): ?>
                    <tr>
                        <td><strong><?= e($r['nama']) ?></strong></td>
                        <td><?= e($r['nomor_kamar'] ?? '-') ?></td>
                        <td><?= tanggal_id($r['tanggal_bayar']) ?></td>
                        <td><?= rupiah($r['jumlah_bayar']) ?></td>
                        <td><span class="badge badge-neutral"><?= e($labelMetode[$r['metode_bayar']] ?? ucfirst($r['metode_bayar'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
