<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_admin.php';

$page_title = 'Pengaturan Kost';
$active     = 'pengaturan';

$set = pengaturan($pdo);

require __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Pengaturan Kost</h1>
        <p class="text-muted mb-0">Informasi ini akan tampil di halaman depan (beranda publik)</p>
    </div>
</div>

<?php render_flash(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Detail Kost</h3>
        <a href="index.php" target="_blank" class="btn btn-outline btn-sm">Lihat Halaman Depan</a>
    </div>
    <form method="POST" action="pengaturan_proses.php">
        <label for="nama_kost">Nama Kost</label>
        <input type="text" id="nama_kost" name="nama_kost" required value="<?= e($set['nama_kost']) ?>">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat"><?= e($set['alamat']) ?></textarea>

        <label for="deskripsi">Deskripsi Kost</label>
        <textarea id="deskripsi" name="deskripsi" placeholder="Ceritakan keunggulan kost Anda"><?= e($set['deskripsi']) ?></textarea>

        <div class="form-row">
            <div>
                <label for="kontak_wa">Nomor WhatsApp</label>
                <input type="text" id="kontak_wa" name="kontak_wa" placeholder="Contoh: 081398913187" value="<?= e($set['kontak_wa']) ?>">
            </div>
            <div>
                <label for="kontak_email">Email Kontak</label>
                <input type="email" id="kontak_email" name="kontak_email" value="<?= e($set['kontak_email']) ?>">
            </div>
        </div>

        <label for="jam_operasional">Jam Operasional</label>
        <input type="text" id="jam_operasional" name="jam_operasional" value="<?= e($set['jam_operasional']) ?>">

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
