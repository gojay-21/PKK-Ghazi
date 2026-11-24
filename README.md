# KelolaKost — Sistem Informasi Manajemen Kost

Aplikasi web **PHP + MySQL** untuk mengelola kost: data kamar, penyewa, tagihan bulanan,
pembayaran (termasuk QRIS simulasi), pengingat jatuh tempo, laporan keuangan, dan portal
khusus penyewa.

Tampilan, alur, dan struktur halaman direkonstruksi agar **sama dengan situs aslinya**:
<https://kelolakost.infinityfreeapp.com>

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-10.4-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/CSS-Custom-0F3D3E)
![Status](https://img.shields.io/badge/status-selesai-2E7D32)
![License](https://img.shields.io/badge/license-portofolio-D4A34C)

---

## Daftar Isi

- [Fitur](#fitur)
- [Screenshot](#screenshot)
- [Teknologi](#teknologi)
- [Struktur Folder](#struktur-folder)
- [Persyaratan](#persyaratan)
- [Cara Menjalankan dari VS Code](#cara-menjalankan-dari-vs-code)
- [Catatan Penting](#catatan-penting)
- [Lisensi](#lisensi)

---

## Fitur

### Halaman Publik
- **Beranda kost** — profil kost, alamat, deskripsi, statistik kamar, dan daftar kamar
  real-time (tersedia/terisi) dengan tombol tanya via WhatsApp.
- **Login terpisah** untuk pengelola dan penyewa.
- **Daftar akun penyewa** — verifikasi memakai nama + No. HP yang sudah didaftarkan pengelola.
- **Lupa password** — verifikasi identitas untuk mengubah email/password.

### Panel Pengelola (Admin)
- **Dashboard** — total kamar, kamar terisi/kosong, penyewa aktif, tagihan belum lunas,
  pendapatan bulan berjalan, dan daftar tagihan mendekati jatuh tempo.
- **Data Kamar** — CRUD kamar (nomor, tipe, harga, fasilitas, status).
- **Penyewa** — CRUD penyewa; status kamar otomatis sinkron (terisi/kosong).
  Dilengkapi **email penagihan** yang boleh berbeda dari email login.
- **Tagihan** — CRUD tagihan bulanan; nominal otomatis mengikuti harga kamar penyewa,
  plus tombol **Kirim** untuk mengirim tagihan ke email penagihan.
- **Pembayaran** — catat pembayaran (tunai/transfer/QRIS/lainnya), lihat bukti, hapus
  pembayaran; status tagihan otomatis berubah lunas/belum lunas.
- **Pengingat Jatuh Tempo** — dikelompokkan menjadi terlambat, ≤ 7 hari, dan akan datang;
  menampilkan email penagihan dan tombol **Kirim Pengingat**.
- **Laporan Keuangan** — rekap pemasukan per bulan/tahun beserta rincian transaksi.
- **Pengaturan Kost** — ubah nama, alamat, deskripsi, kontak, jam operasional yang tampil
  di beranda.
- **Kelola Admin** — tambah admin, reset password, dan hapus akun admin lain.

### Portal Penyewa
- Melihat data kamar, email penagihan, tagihan, dan riwayat pembayaran.
- **Bayar QRIS (simulasi)** — menampilkan QR dummy, lalu mencatat pembayaran dan
  menampilkan **bukti pembayaran QRIS** yang bisa dilihat, dicetak, atau disimpan
  sebagai PDF.
- Referensi pembayaran QRIS tampil di riwayat dan pada bukti pembayaran.
- Mengubah email dan password akun sendiri.

---

## Screenshot

### 1. Beranda Publik

Halaman depan menampilkan profil kost, statistik kamar, dan daftar kamar beserta status
ketersediaannya secara real-time.

![Beranda](docs/screenshots/01-beranda.png)

### 2. Login Pengelola

Autentikasi khusus admin/pengelola kost.

![Login Pengelola](docs/screenshots/02-login-pengelola.png)

### 3. Login Penyewa

Autentikasi khusus penghuni kost.

![Login Penyewa](docs/screenshots/03-login-penyewa.png)

### 4. Lupa Password

Pemulihan akun dengan verifikasi nama dan No. HP (penyewa) atau username dan nama lengkap
(pengelola).

![Lupa Password](docs/screenshots/04-lupa-password.png)

### 5. Dashboard Pengelola

Ringkasan kondisi kost: jumlah kamar, kamar terisi/kosong, penyewa aktif, tagihan belum
lunas, pendapatan bulan ini, dan tagihan yang mendekati jatuh tempo.

![Dashboard](docs/screenshots/05-dashboard.png)

### 6. Data Kamar

Mengelola kamar beserta tipe, harga sewa, fasilitas, dan statusnya.

![Data Kamar](docs/screenshots/06-data-kamar.png)

### 7. Data Penyewa

Mengelola data penghuni, termasuk **email penagihan** yang bisa berbeda dari email login.
Jika dikosongkan, sistem otomatis memakai email login. Saat penyewa ditambahkan, kamar
otomatis berubah menjadi **Terisi**; saat dihapus, kamar kembali **Kosong**.

![Data Penyewa](docs/screenshots/07-data-penyewa.png)

### 8. Tagihan

Membuat tagihan bulanan. Jumlah tagihan otomatis terisi sesuai harga kamar penyewa dan
masih bisa diubah manual. Tombol **Kirim** membuka email berisi rincian tagihan ke
email penagihan penyewa.

![Tagihan](docs/screenshots/08-tagihan.png)

### 9. Pembayaran

Mencatat pembayaran dan menampilkan riwayat. Menyimpan pembayaran otomatis melunasi
tagihan; menghapusnya mengembalikan status menjadi belum lunas.

![Pembayaran](docs/screenshots/09-pembayaran.png)

### 10. Pengingat Jatuh Tempo

Tagihan dikelompokkan menjadi **terlambat**, **jatuh tempo ≤ 7 hari**, dan **akan datang**.
Setiap baris menampilkan email penagihan dan tombol **Kirim Pengingat**.

![Pengingat Jatuh Tempo](docs/screenshots/10-pengingat.png)

### 11. Laporan Keuangan

Rekap pemasukan berdasarkan bulan dan tahun, lengkap dengan rincian transaksi.

![Laporan Keuangan](docs/screenshots/11-laporan-keuangan.png)

### 12. Pengaturan Kost

Mengubah informasi kost yang tampil di halaman depan publik.

![Pengaturan Kost](docs/screenshots/12-pengaturan.png)

### 13. Kelola Admin

Menambah akun pengelola lain, reset password, dan menghapus akun admin.

![Kelola Admin](docs/screenshots/13-kelola-admin.png)

### 14. Portal Penyewa

Halaman khusus penghuni: data kamar, email penagihan, daftar tagihan, riwayat pembayaran
dengan nomor referensi QRIS, serta tombol **Lihat / Cetak Bukti**.

![Portal Penyewa](docs/screenshots/14-portal-penyewa.png)

### 15. Pembayaran QRIS (Simulasi)

Menampilkan QR pembayaran. Pada mode demo, QR bersifat **dummy** dan tidak memproses uang
sungguhan.

![Bayar QRIS](docs/screenshots/15-bayar-qris.png)

### 16. Bukti Pembayaran

Bukti otomatis setelah pembayaran berhasil. Menampilkan nomor referensi QRIS, email
penagihan, dan keterangan simulasi; dapat dicetak atau disimpan sebagai PDF melalui browser.

![Bukti Pembayaran](docs/screenshots/16-bukti-pembayaran.png)

---

## Teknologi

| Bagian | Keterangan |
|---|---|
| Bahasa | PHP 8.x (native, tanpa framework) |
| Database | MySQL / MariaDB (XAMPP) |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| QR Code | [`qrcodejs`](https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js) via CDN |
| Font | Google Fonts — Poppins & Inter |
| Keamanan | `password_hash()` / `password_verify()`, PDO prepared statements, session |

---

## Struktur Folder

```text
PKK_Ghazi/
├── .env.example               # contoh konfigurasi (salin menjadi .env)
├── .gitignore
├── assets/
│   ├── css/style.css          # seluruh styling aplikasi
│   └── js/script.js           # konfirmasi hapus, auto-hide alert, menu aktif
├── docs/
│   └── screenshots/           # screenshot untuk README
├── includes/
│   ├── auth_admin.php         # guard halaman admin
│   ├── auth_penyewa.php       # guard halaman penyewa
│   ├── footer.php             # penutup layout admin
│   ├── functions.php          # helper (rupiah, tanggal, flash, sinkron status)
│   └── header.php             # sidebar + layout admin
├── koneksi.php                # baca .env, bootstrap & koneksi PDO
├── database.sql               # skema + data demo
├── migrasi_email_penagihan.sql # migrasi kolom email penagihan (database lama)
├── index.php                  # beranda publik
├── login.php                  # login pengelola
├── login_penyewa.php          # login penyewa
├── daftar.php                 # pendaftaran akun penyewa
├── lupa_password.php          # reset password mandiri
├── dashboard.php
├── kamar.php / kamar_proses.php
├── penyewa.php / penyewa_proses.php
├── tagihan.php / tagihan_proses.php
├── pembayaran.php / pembayaran_proses.php
├── pengingat.php
├── laporan.php
├── pengaturan.php / pengaturan_proses.php
├── kelola_admin.php / kelola_admin_proses.php
├── reset_password_admin.php
├── portal_penyewa.php
├── ubah_profil_penyewa.php
├── bayar_qris.php / qris_proses.php
├── bukti_pembayaran.php
└── logout.php / logout_penyewa.php
```

---

## Persyaratan

- **XAMPP** (PHP 8.x + MySQL/MariaDB) — <https://www.apachefriends.org>
- **VS Code** — <https://code.visualstudio.com>
- Browser modern (Chrome/Edge/Firefox)

> Tidak perlu `composer` atau `npm`. Semua dependensi frontend dimuat via CDN.

---

## Cara Menjalankan dari VS Code

### Langkah 1 — Buka project di VS Code

1. Buka **VS Code**.
2. Pilih **File → Open Folder…**
3. Pilih folder **`D:\PKK_Ghazi`**.

Struktur folder akan terlihat di panel Explorer sebelah kiri.

### Langkah 2 — Nyalakan MySQL

1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada baris **MySQL** (Apache tidak wajib jika memakai PHP built-in server).
3. Pastikan status MySQL menjadi hijau dan port `3306` aktif.

### Langkah 3 — Import database

**Cara A — phpMyAdmin (paling mudah):**

1. Buka <http://localhost/phpmyadmin>.
2. Klik tab **Import**.
3. Pilih file `D:\PKK_Ghazi\database.sql`, lalu klik **Go/Import**.
4. Database `kelolakost` beserta seluruh tabel dan data demo akan dibuat otomatis.

**Cara B — Terminal VS Code:**

Buka terminal di VS Code (**Terminal → New Terminal**), lalu jalankan:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -u root --default-character-set=utf8mb4 -e "source D:/PKK_Ghazi/database.sql"
```

> **Sudah punya database lama?** Jalankan migrasi berikut sekali agar kolom
> `email_penagihan` ditambahkan tanpa menghapus data:
>
> ```powershell
> & 'C:\xampp\mysql\bin\mysql.exe' -u root --default-character-set=utf8mb4 kelolakost -e "source D:/PKK_Ghazi/migrasi_email_penagihan.sql"
> ```

### Langkah 4 — Sesuaikan konfigurasi (jika perlu)

Konfigurasi database dibaca dari file `.env`. Jika belum ada, salin contohnya:

```powershell
Copy-Item .env.example .env
```

Lalu sesuaikan isinya:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=kelolakost
DB_USER=root
DB_PASS=
```

Nilai default di atas sudah cocok untuk XAMPP (root tanpa password). Jika MySQL Anda
memakai password, isi `DB_PASS` sesuai konfigurasi.

> File `.env` tidak ikut di-commit. Yang dibagikan di repo hanya `.env.example`.

### Langkah 5 — Jalankan aplikasi

**Cara A — PHP built-in server (direkomendasikan):**

Di terminal VS Code (pastikan berada di folder project):

```powershell
php -S localhost:8000
```

Lalu buka <http://localhost:8000> di browser.

> Jika perintah `php` tidak dikenali, gunakan path lengkap:
> `& 'C:\xampp\php\php.exe' -S localhost:8000`

**Cara B — XAMPP Apache:**

1. Salin folder `PKK_Ghazi` ke `C:\xampp\htdocs\`.
2. Start **Apache** di XAMPP Control Panel.
3. Buka <http://localhost/PKK_Ghazi>.

### Langkah 6 — Login

Setelah database di-import, langsung masuk memakai **akun demo** berikut (ketik apa adanya,
bukan hash):

| Halaman | Username / Email | Password |
|---|---|---|
| Login Pengelola — `login.php` | `admin` | `admin123` |
| Login Pengelola (akun kedua) | `gojay` | `admin123` |
| Login Penyewa — `login_penyewa.php` | `gojay@demo.test` | `penyewa123` |
| Login Penyewa (akun kedua) | `gogo@demo.test` | `penyewa123` |

> Password disimpan di database dalam bentuk hash `password_hash()`. Anda cukup mengetik
> password aslinya (`admin123` / `penyewa123`) di form login — sistem yang mencocokkan hash-nya.

Jika perlu membuat akun admin baru, buat hash password terlebih dahulu:

```powershell
php -r "echo password_hash('PASSWORD_ANDA', PASSWORD_DEFAULT), PHP_EOL;"
```

Lalu masukkan akun tersebut ke tabel `admin`:

```sql
INSERT INTO admin (nama_lengkap, username, password)
VALUES ('Nama Anda', 'username_anda', 'HASIL_HASH_DI_ATAS');
```

### Menghentikan aplikasi

- Tekan `Ctrl + C` di terminal untuk mematikan PHP server.
- Klik **Stop** pada MySQL di XAMPP Control Panel.

### Tips VS Code

- Ekstensi yang membantu: **PHP Intelephense**, **PHP Debug**, **Live Server** (opsional).
- Untuk melihat error PHP saat development, tambahkan di awal `koneksi.php`:
  ```php
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
  ```

---

## Catatan Penting

- **QRIS masih simulasi.** QR yang tampil adalah payload dummy, dan tombol
  *“Saya Sudah Membayar (Simulasi)”* hanya mencatat pembayaran ke database — tidak ada
  transaksi uang sungguhan.
- **Email penagihan memakai `mailto:`**, bukan SMTP. Tombol *Kirim* dan *Kirim Pengingat*
  membuka aplikasi email bawaan pengelola dengan subjek dan isi yang sudah terisi, jadi
  tidak perlu mengatur kredensial email di aplikasi.
- **Status kamar otomatis** mengikuti ada/tidaknya penyewa aktif pada kamar tersebut.
- **Status tagihan otomatis** menjadi *Lunas* ketika total pembayaran ≥ jumlah tagihan,
  dan kembali *Belum Lunas* jika pembayaran dihapus.
- **Keamanan**: password disimpan dengan `password_hash()`, seluruh query memakai PDO
  prepared statement, dan setiap halaman admin/penyewa dilindungi session guard.
- **Reset data demo**: import ulang `database.sql` kapan saja untuk mengembalikan kondisi awal.

---

## Lisensi

Project ini dibuat untuk keperluan **pembelajaran dan portofolio**. Silakan gunakan dan
kembangkan dengan menyebut sumbernya.
