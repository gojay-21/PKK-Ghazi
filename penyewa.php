<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Penyewa';
$active     = 'penyewa';

$editId = (int) ($_GET['edit'] ?? 0);
$edit   = null;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM penyewa WHERE id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch() ?: null;
}

if ($edit) {
    $stmt = $pdo->prepare("SELECT * FROM kamar WHERE status = 'kosong' OR id = ? ORDER BY nomor_kamar ASC");
    $stmt->execute([(int) $edit['kamar_id']]);
    $pilihanKamar = $stmt->fetchAll();
} else {
    $pilihanKamar = $pdo->query("SELECT * FROM kamar WHERE status = 'kosong' ORDER BY nomor_kamar ASC")->fetchAll();
}

$daftarPenyewa = $pdo->query('
    SELECT p.*, k.nomor_kamar
    FROM penyewa p
    LEFT JOIN kamar k ON k.id = p.kamar_id
    ORDER BY p.nama ASC
')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Data Penyewa</h1>
        <p class="text-muted mb-0">Kelola data penghuni kost</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0"><?= $edit ? 'Edit Penyewa' : 'Tambah Penyewa Baru' ?></h3>
    </div>
    <form method="POST" action="penyewa_proses.php">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : '' ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">

        <div class="form-row">
            <div>
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required value="<?= e($edit['nama'] ?? '') ?>">
            </div>
            <div>
                <label for="kamar_id">Kamar</label>
                <select id="kamar_id" name="kamar_id" required>
                    <option value="">Pilih Kamar</option>
                    <?php foreach ($pilihanKamar as $k): ?>
                        <option value="<?= (int) $k['id'] ?>"<?= (int) ($edit['kamar_id'] ?? 0) === (int) $k['id'] ? ' selected' : '' ?>>
                            <?= e($k['nomor_kamar']) ?> — <?= e($k['tipe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="no_hp">No. HP</label>
                <input type="text" id="no_hp" name="no_hp" value="<?= e($edit['no_hp'] ?? '') ?>">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($edit['email'] ?? '') ?>">
            </div>
        </div>

        <label for="email_penagihan">Email Penagihan (opsional)</label>
        <input type="email" id="email_penagihan" name="email_penagihan" placeholder="Kosongkan untuk memakai email di atas" value="<?= e($edit['email_penagihan'] ?? '') ?>">
        <p class="text-muted" style="font-size:12px; margin-top:-8px;">
            Dipakai untuk mengirim tagihan dan pengingat. Jika kosong, sistem memakai email login penyewa.
        </p>

        <div class="form-row">
            <div>
                <label for="no_ktp">No. KTP</label>
                <input type="text" id="no_ktp" name="no_ktp" value="<?= e($edit['no_ktp'] ?? '') ?>">
            </div>
            <div>
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date" id="tanggal_masuk" name="tanggal_masuk" required value="<?= e($edit['tanggal_masuk'] ?? date('Y-m-d')) ?>">
            </div>
        </div>

        <label for="alamat_asal">Alamat Asal</label>
        <textarea id="alamat_asal" name="alamat_asal"><?= e($edit['alamat_asal'] ?? '') ?></textarea>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="aktif"<?= ($edit['status'] ?? 'aktif') === 'aktif' ? ' selected' : '' ?>>Aktif</option>
            <option value="nonaktif"<?= ($edit['status'] ?? '') === 'nonaktif' ? ' selected' : '' ?>>Nonaktif</option>
        </select>

        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Penyewa' ?></button>
        <?php if ($edit): ?>
            <a href="penyewa.php" class="btn btn-outline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Daftar Penyewa</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kamar</th>
                <th>No. HP</th>
                <th>Email Penagihan</th>
                <th>Tanggal Masuk</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daftarPenyewa as $p): ?>
                <?php $aktif = $p['status'] === 'aktif'; ?>
                <tr>
                    <td><strong><?= e($p['nama']) ?></strong></td>
                    <td><?= e($p['nomor_kamar'] ?? '-') ?></td>
                    <td><?= e($p['no_hp']) ?></td>
                    <td>
                        <?php $emailTagih = email_penagihan($p); ?>
                        <?php if ($emailTagih !== ''): ?>
                            <a href="mailto:<?= e($emailTagih) ?>"><?= e($emailTagih) ?></a>
                            <?php if (empty($p['email_penagihan'])): ?>
                                <span class="text-muted" style="font-size:11px;">(ikut email login)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= tanggal_id($p['tanggal_masuk']) ?></td>
                    <td>
                        <span class="badge <?= $aktif ? 'badge-success' : 'badge-neutral' ?>">
                            <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <a href="penyewa.php?edit=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <a href="penyewa_proses.php?action=delete&id=<?= (int) $p['id'] ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm="Hapus data penyewa <?= e($p['nama']) ?>?">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
