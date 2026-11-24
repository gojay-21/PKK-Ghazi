<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Pembayaran';
$active     = 'pembayaran';

$tagihanBelum = $pdo->query("
    SELECT t.id, t.jumlah_tagihan, t.periode_bulan, t.periode_tahun, p.nama,
           (t.jumlah_tagihan - COALESCE((SELECT SUM(jumlah_bayar) FROM pembayaran WHERE tagihan_id = t.id), 0)) AS sisa
    FROM tagihan t
    JOIN penyewa p ON p.id = t.penyewa_id
    WHERE t.status = 'belum_lunas'
    ORDER BY p.nama ASC, t.id ASC
")->fetchAll();

$riwayat = $pdo->query('
    SELECT b.*, t.periode_bulan, t.periode_tahun, p.nama
    FROM pembayaran b
    JOIN tagihan t ON t.id = b.tagihan_id
    JOIN penyewa p ON p.id = t.penyewa_id
    ORDER BY b.tanggal_bayar DESC, b.id ASC
')->fetchAll();

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
        <h1>Pembayaran</h1>
        <p class="text-muted mb-0">Catat pembayaran tagihan dari penyewa</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Catat Pembayaran Baru</h3>
    </div>

    <form method="POST" action="pembayaran_proses.php">
        <input type="hidden" name="action" value="create">

        <div class="form-row">
            <div>
                <label for="tagihan_id">Tagihan</label>
                <select id="tagihan_id" name="tagihan_id" required>
                    <option value="">Pilih Tagihan</option>
                    <?php foreach ($tagihanBelum as $t): ?>
                        <option value="<?= (int) $t['id'] ?>" data-sisa="<?= e($t['sisa']) ?>">
                            <?= e($t['nama']) ?> — <?= bulan_singkat((int) $t['periode_bulan']) ?> <?= (int) $t['periode_tahun'] ?>
                            (<?= rupiah($t['sisa']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="jumlah_bayar">Jumlah Dibayar (Rp)</label>
                <input type="number" id="jumlah_bayar" name="jumlah_bayar" required min="0" step="1000">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="tanggal_bayar">Tanggal Bayar</label>
                <input type="date" id="tanggal_bayar" name="tanggal_bayar" required value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label for="metode_bayar">Metode Pembayaran</label>
                <select id="metode_bayar" name="metode_bayar" onchange="document.getElementById('wa-reminder').style.display = (this.value === 'transfer') ? 'block' : 'none';">
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <div id="wa-reminder" class="wa-note" style="display:none; margin-top:-8px; padding:12px 16px; border-radius:8px; background:var(--color-warning-bg); color:#8A6116; font-size:14px;">
                    Konfirmasi/cek bukti transfer ke WA:
                    <strong><a href="https://wa.me/<?= e(preg_replace('/\D+/', '', (string) pengaturan($pdo)['kontak_wa'])) ?>" target="_blank" style="color:inherit; text-decoration:underline;"><?= e(pengaturan($pdo)['kontak_wa']) ?></a></strong>
                </div>
            </div>
        </div>

        <label for="catatan">Catatan</label>
        <input type="text" id="catatan" name="catatan" placeholder="Opsional">

        <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
    </form>
    <p class="text-muted" style="font-size:13px;">Menyimpan pembayaran akan otomatis menandai tagihan terkait sebagai <strong>Lunas</strong>.</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Riwayat Pembayaran</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Penyewa</th>
                <th>Periode</th>
                <th>Tanggal Bayar</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $b): ?>
                <tr>
                    <td><strong><?= e($b['nama']) ?></strong></td>
                    <td><?= bulan_singkat((int) $b['periode_bulan']) . ' ' . (int) $b['periode_tahun'] ?></td>
                    <td><?= tanggal_id($b['tanggal_bayar']) ?></td>
                    <td><?= rupiah($b['jumlah_bayar']) ?></td>
                    <td><span class="badge badge-neutral"><?= e($labelMetode[$b['metode_bayar']] ?? ucfirst($b['metode_bayar'])) ?></span></td>
                    <td class="actions-cell">
                        <a href="bukti_pembayaran.php?id=<?= (int) $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm">Lihat Bukti</a>
                        <a href="pembayaran_proses.php?action=delete&id=<?= (int) $b['id'] ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm="Hapus riwayat pembayaran ini? Tagihan akan kembali berstatus belum lunas.">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
