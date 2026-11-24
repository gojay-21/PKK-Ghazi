<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Data Kamar';
$active     = 'kamar';

$editId = (int) ($_GET['edit'] ?? 0);
$edit   = null;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM kamar WHERE id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch() ?: null;
}

$daftarKamar = $pdo->query('SELECT * FROM kamar ORDER BY id ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Data Kamar</h1>
        <p class="text-muted mb-0">Kelola daftar kamar, tipe, dan harga sewa</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0"><?= $edit ? 'Edit Kamar' : 'Tambah Kamar Baru' ?></h3>
    </div>
    <form method="POST" action="kamar_proses.php">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : '' ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">

        <div class="form-row">
            <div>
                <label for="nomor_kamar">Nomor Kamar</label>
                <input type="text" id="nomor_kamar" name="nomor_kamar" required value="<?= e($edit['nomor_kamar'] ?? '') ?>">
            </div>
            <div>
                <label for="tipe">Tipe Kamar</label>
                <input type="text" id="tipe" name="tipe" required placeholder="Standar / VIP / dsb" value="<?= e($edit['tipe'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="harga_bulanan">Harga per Bulan (Rp)</label>
                <input type="number" id="harga_bulanan" name="harga_bulanan" required min="0" step="1000" value="<?= e($edit['harga_bulanan'] ?? '') ?>">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="kosong"<?= ($edit['status'] ?? '') === 'kosong' ? ' selected' : '' ?>>Kosong</option>
                    <option value="terisi"<?= ($edit['status'] ?? '') === 'terisi' ? ' selected' : '' ?>>Terisi</option>
                </select>
            </div>
        </div>

        <label for="fasilitas">Fasilitas</label>
        <textarea id="fasilitas" name="fasilitas" placeholder="Contoh: Kasur, Lemari, AC, Kamar Mandi Dalam"><?= e($edit['fasilitas'] ?? '') ?></textarea>

        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Kamar' ?></button>
        <?php if ($edit): ?>
            <a href="kamar.php" class="btn btn-outline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Daftar Kamar</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Kamar</th>
                <th>Tipe</th>
                <th>Harga/Bulan</th>
                <th>Fasilitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daftarKamar as $k): ?>
                <?php $terisi = $k['status'] === 'terisi'; ?>
                <tr>
                    <td><strong><?= e($k['nomor_kamar']) ?></strong></td>
                    <td><?= e($k['tipe']) ?></td>
                    <td><?= rupiah($k['harga_bulanan']) ?></td>
                    <td class="text-muted"><?= e($k['fasilitas']) ?></td>
                    <td>
                        <span class="badge <?= $terisi ? 'badge-success' : 'badge-neutral' ?>">
                            <?= $terisi ? 'Terisi' : 'Kosong' ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <a href="kamar.php?edit=<?= (int) $k['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <a href="kamar_proses.php?action=delete&id=<?= (int) $k['id'] ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm="Hapus kamar <?= e($k['nomor_kamar']) ?>?">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
