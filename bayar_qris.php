<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/auth_penyewa.php';

$tagihanId = (int) ($_GET['tagihan_id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT t.*, p.nama, k.nomor_kamar
    FROM tagihan t
    JOIN penyewa p ON p.id = t.penyewa_id
    LEFT JOIN kamar k ON k.id = p.kamar_id
    WHERE t.id = ? AND t.penyewa_id = ?
');
$stmt->execute([$tagihanId, (int) $_SESSION['penyewa_id']]);
$tagihan = $stmt->fetch();

if (!$tagihan) {
    flash('danger', 'Tagihan tidak ditemukan.');
    redirect('portal_penyewa.php');
}

if ($tagihan['status'] === 'lunas') {
    flash('warning', 'Tagihan ini sudah lunas.');
    redirect('portal_penyewa.php');
}

$payload = '00020101021226670016COM.EXAMPLE.WWW0118936009123456789031CC015EEF5B5204549953033609750000.005802ID5910KELOLAKOST6007JAKARTA63041234';
?>
<!DOCTYPE html>
<html lang="id"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayar QRIS — KelolaKost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<div class="login-page">
    <div class="login-card" style="max-width:420px;">
        <div class="brand">Kelola<span style="color:#D4A34C">Kost</span></div>
        <div class="tagline">Pembayaran QRIS</div>

        <div class="qris-simulasi-badge">MODE UJI COBA / SIMULASI</div>

        <div class="qris-box">
            <div id="qrcode"></div>
        </div>

        <table style="text-align:left; margin-top:16px;">
            <tbody>
                <tr><td class="text-muted">Penyewa</td><td><strong><?= e($tagihan['nama']) ?></strong></td></tr>
                <tr><td class="text-muted">Kamar</td><td><?= e($tagihan['nomor_kamar'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Periode</td><td><?= bulan_nama((int) $tagihan['periode_bulan']) . ' ' . (int) $tagihan['periode_tahun'] ?></td></tr>
                <tr><td class="text-muted">Jumlah Bayar</td><td><strong><?= rupiah($tagihan['jumlah_tagihan']) ?></strong></td></tr>
            </tbody>
        </table>

        <p class="text-muted" style="font-size:12px; margin-top:12px;">
            Scan QR di atas menggunakan aplikasi e-wallet/mobile banking apa pun yang mendukung QRIS.
            <br><em>(Karena ini mode uji coba, QR di atas acak/dummy dan tidak akan memproses uang sungguhan.)</em>
        </p>

        <form method="POST" action="qris_proses.php">
            <input type="hidden" name="tagihan_id" value="<?= (int) $tagihan['id'] ?>">
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
                Saya Sudah Membayar (Simulasi)
            </button>
        </form>
        <p class="text-muted mt-16" style="font-size:12px;">
            <a href="portal_penyewa.php">← Batal, kembali ke Portal</a>
        </p>
    </div>
</div>

<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= e($payload) ?>",
        width: 220,
        height: 220,
        colorDark: "#0F3D3E",
        colorLight: "#ffffff"
    });
</script>

</body></html>
