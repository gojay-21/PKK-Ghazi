<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Tagihan';
$active     = 'tagihan';
$set        = pengaturan($pdo);

$editId = (int) ($_GET['edit'] ?? 0);
$edit   = null;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tagihan WHERE id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch() ?: null;
}

$daftarPenyewa = $pdo->query('
    SELECT p.id, p.nama, k.nomor_kamar, k.harga_bulanan
    FROM penyewa p
    JOIN kamar k ON k.id = p.kamar_id
    WHERE p.status = \'aktif\'
    ORDER BY p.nama ASC
')->fetchAll();

$daftarTagihan = $pdo->query('
    SELECT t.*, p.nama, p.email, p.email_penagihan, k.nomor_kamar
    FROM tagihan t
    JOIN penyewa p ON p.id = t.penyewa_id
    LEFT JOIN kamar k ON k.id = p.kamar_id
    ORDER BY t.periode_tahun DESC, t.periode_bulan DESC, t.id ASC
')->fetchAll();

$bulanTerpilih = $edit ? (int) $edit['periode_bulan'] : (int) date('n');
$tahunTerpilih = $edit ? (int) $edit['periode_tahun'] : (int) date('Y');

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Tagihan</h1>
        <p class="text-muted mb-0">Kelola tagihan sewa bulanan penyewa</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0"><?= $edit ? 'Edit Tagihan' : 'Buat Tagihan Baru' ?></h3>
    </div>
    <form method="POST" action="tagihan_proses.php">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : '' ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">

        <div class="form-row">
            <div>
                <label for="penyewa_id">Penyewa</label>
                <select id="penyewa_id" name="penyewa_id" required onchange="isiOtomatisJumlahTagihan(this)">
                    <option value="">Pilih Penyewa</option>
                    <?php foreach ($daftarPenyewa as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" data-harga="<?= e($p['harga_bulanan']) ?>"<?= (int) ($edit['penyewa_id'] ?? 0) === (int) $p['id'] ? ' selected' : '' ?>>
                            <?= e($p['nama']) ?> — Kamar <?= e($p['nomor_kamar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-muted" style="font-size:12px; margin-top:4px;">Jumlah tagihan otomatis terisi sesuai harga kamar penyewa (bisa diubah manual bila perlu).</p>
            </div>
            <div>
                <label for="jumlah_tagihan">Jumlah Tagihan (Rp)</label>
                <input type="number" id="jumlah_tagihan" name="jumlah_tagihan" required min="0" step="1000" value="<?= e($edit['jumlah_tagihan'] ?? '') ?>">
            </div>
        </div>
        <script>
        function isiOtomatisJumlahTagihan(select) {
            var opt = select.options[select.selectedIndex];
            var harga = opt.getAttribute('data-harga');
            if (harga) {
                document.getElementById('jumlah_tagihan').value = harga;
            }
        }
        </script>

        <div class="form-row">
            <div>
                <label for="periode_bulan">Periode Bulan</label>
                <select id="periode_bulan" name="periode_bulan" required>
                    <?php for ($b = 1; $b <= 12; $b++): ?>
                        <option value="<?= $b ?>"<?= $bulanTerpilih === $b ? ' selected' : '' ?>>
                            <?= bulan_nama($b) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="periode_tahun">Periode Tahun</label>
                <input type="number" id="periode_tahun" name="periode_tahun" required value="<?= $tahunTerpilih ?>">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
                <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required value="<?= e($edit['tanggal_jatuh_tempo'] ?? '') ?>">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="belum_lunas"<?= ($edit['status'] ?? '') !== 'lunas' ? ' selected' : '' ?>>Belum Lunas</option>
                    <option value="lunas"<?= ($edit['status'] ?? '') === 'lunas' ? ' selected' : '' ?>>Lunas</option>
                </select>
            </div>
        </div>

        <label for="keterangan">Keterangan</label>
        <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Tagihan sewa bulan Agustus" value="<?= e($edit['keterangan'] ?? '') ?>">

        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Buat Tagihan' ?></button>
        <?php if ($edit): ?>
            <a href="tagihan.php" class="btn btn-outline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Daftar Tagihan</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Penyewa</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daftarTagihan as $t): ?>
                <?php
                $lunas      = $t['status'] === 'lunas';
                $emailTagih = email_penagihan($t);
                $periode    = bulan_nama((int) $t['periode_bulan']) . ' ' . (int) $t['periode_tahun'];
                $subjek     = 'Tagihan ' . $set['nama_kost'] . ' — ' . $periode;
                $isi        = "Halo " . $t['nama'] . ",\n\n"
                    . 'Tagihan sewa kamar ' . ($t['nomor_kamar'] ?? '-') . ' untuk periode ' . $periode . ' sebesar ' . rupiah($t['jumlah_tagihan']) . " telah diterbitkan.\n"
                    . 'Jatuh tempo: ' . tanggal_id($t['tanggal_jatuh_tempo']) . ".\n\n"
                    . "Silakan melakukan pembayaran melalui portal penyewa.\n\n"
                    . 'Terima kasih,' . "\n" . $set['nama_kost'];
                ?>
                <tr>
                    <td><strong><?= e($t['nama']) ?></strong></td>
                    <td><?= bulan_nama((int) $t['periode_bulan']) . ' ' . (int) $t['periode_tahun'] ?></td>
                    <td><?= rupiah($t['jumlah_tagihan']) ?></td>
                    <td><?= tanggal_id($t['tanggal_jatuh_tempo']) ?></td>
                    <td>
                        <span class="badge <?= $lunas ? 'badge-success' : 'badge-danger' ?>">
                            <?= $lunas ? 'Lunas' : 'Belum Lunas' ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <?php if (!$lunas && $emailTagih !== ''): ?>
                            <a href="<?= e(link_mailto($emailTagih, $subjek, $isi)) ?>" class="btn btn-outline btn-sm">Kirim</a>
                        <?php endif; ?>
                        <a href="tagihan.php?edit=<?= (int) $t['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <a href="tagihan_proses.php?action=delete&id=<?= (int) $t['id'] ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm="Hapus tagihan ini?">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
