# Portal PPDB Online SMPS 2 Al-Muhajirin Purwakarta

<p align="center">
  <img src="public/logo/logo.png" alt="Logo SMPS2 Al-Muhajirin" width="120" style="margin-bottom:10px;">
  <br>
  <strong>Tahun Pelajaran 2027–2028</strong>
  <br>
  <em>Sistem Penerimaan Peserta Didik Baru Terintegrasi (Frontend Consumer & Admin Management Portal)</em>
</p>

---

## 📌 Tentang Proyek

Aplikasi **PPDB Online SMPS 2 Al-Muhajirin Purwakarta** adalah portal admisi mandiri berbasis web yang melayani seluruh siklus pendaftaran calon santri baru: mulai dari registrasi akun, pemilihan peminatan (Reguler, Bahasa, Tahfidz, ICT), pembayaran biaya pendaftaran (Transfer Bank Mandiri & Tunai di Loket TU), unggah berkas persyaratan digital (KK, Akta, NISN, Pas Foto), pengisian biodata terpadu standar Dapodik/SIAKAD, verifikasi panitia, hingga pencetakan **Kartu Tes Peserta standar ukuran A6**.

Aplikasi ini beroperasi dengan model **Hybrid Architecture**:
1. **Lokal MySQL Database**: Menyimpan data pendaftar secara cepat, tangguh, dan independen di server hosting lokal.
2. **FastAPI Backend Consumer**: Mengonsumsi dan menyinkronkan data pendaftar yang diterima ke Master Data SIAKAD Pusat secara *one-click* atau *background cron job*.
3. **Telegram Bot Notification**: Mengirim notifikasi otomatis ke grup Telegram panitia saat ada calon santri yang mengunggah berkas bukti pembayaran.

---

## ✨ Fitur-Fitur Utama

### 👨‍🎓 Portal Calon Siswa
- **Landing Page Interaktif**: Informasi profil sekolah, alur pendaftaran, fasilitas, dan tabel **Rincian Biaya Resmi TP 2027–2028** (Kelas Reguler vs Kelas Takhosus) sesuai dokumen yayasan.
- **Autentikasi Mandiri**: Registrasi akun dan login pendaftar dengan proteksi rate-limiting & session security.
- **Dua Pilihan Pembayaran**:
  - **Transfer Bank**: Rekening Bank Mandiri `1730004960275` a.n. `PUTRI MUNAWWAROH` (unggah foto/screenshot m-Banking atau struk ATM).
  - **Tunai (Cash di Loket)**: Pembayaran tunai di Loket Tata Usaha Gedung SMPS 2 Al-Muhajirin (unggah foto kuitansi resmi).
- **Pengunggahan 4 Berkas Persyaratan**: Kartu Keluarga (KK), Akta Kelahiran, Kartu NISN, dan Pas Foto 3×4 latar merah.
- **Formulir Biodata Lengkap (6 Langkah)**: Data pokok, identitas tambahan, alamat & domisili, kontak, data orang tua (Ayah, Ibu, Wali), serta konfirmasi pernyataan.
- **Cetak Kartu Peserta Tes (A6)**: Menampilkan data pokok, nomor registrasi, jadwal ujian, kop resmi sekolah, serta **Pas Foto 3×4 calon siswa** yang telah disesuaikan dengan proporsi cetak standar kertas A6.

### 🛡️ Portal Admin PPDB (Panitia & Tata Usaha)
- **Dashboard Monitoring Real-Time**: Statistik calon santri, status verifikasi berkas, penerimaan, dan status sinkronisasi.
- **Verifikasi Pembayaran Satu Klik**: Menyetujui (*approve*) atau menolak (*reject*) bukti pembayaran calon santri dengan catatan panitia.
- **Manajemen Berkas Digital**: Pratinjau (*preview*) foto/PDF bukti bayar dan berkas identitas calon santri secara langsung.
- **Manajemen Akun Calon Siswa**: Tambah akun baru, edit data, hapus, ekspor data, dan impor akun massal via template Excel.
- **Penerimaan Calon Siswa**: Menyetujui kelulusan seleksi calon santri baru.
- **Sinkronisasi Master Data API**: Tombol *One-Click Bulk Sync* untuk mengirim data siswa yang belum tersinkron ke API backend SIAKAD.

---

## 🛠️ Tech Stack & Persyaratan Sistem

- **Framework**: Laravel 10.x
- **Bahasa Pemrograman**: PHP >= 8.1
- **Database**: MySQL >= 5.7 atau MariaDB >= 10.3
- **Ekstensi PHP Wajib**:
  - `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `Filter`, `Hash`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `pdo_mysql`, `Session`, `Tokenizer`, `XML`, `Zip`
- **Frontend UI**: Bootstrap 5.3, Material Symbols Outlined, Phosphor Icons, Google Fonts (Outfit & Plus Jakarta Sans).

---

## 💻 Panduan Instalasi Lokal (Laragon / XAMPP)

1. **Clone atau Letakkan File Proyek**:
   Letakkan direktori proyek di folder root web server lokal Anda:
   ```bash
   # Contoh pada Laragon:
   c:\laragon\www\ppdb-frontend
   ```

2. **Install Dependensi Composer**:
   ```bash
   composer install
   ```

3. **Salin File Environment**:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database di `.env`**:
   Buka file `.env` dan sesuaikan koneksi database MySQL lokal:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ppdb_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migrasi & Seeder Database**:
   ```bash
   php artisan migrate --seed
   ```

7. **Buat Symlink Penyimpanan Berkas**:
   ```bash
   php artisan storage:link
   ```

8. **Jalankan Aplikasi**:
   - Jika menggunakan Laragon: Akses langsung via `http://ppdb-frontend.test`
   - Atau menggunakan artisan serve:
     ```bash
     php artisan serve
     ```
     Buka di browser: `http://127.0.0.1:8000`

---

## 🚀 Panduan Deployment ke cPanel (Shared Hosting)

Berikut adalah panduan lengkap menyiapkan dan meluncurkan aplikasi ke server cPanel tanpa membutuhkan akses SSH:

### 1. Struktur File di File Manager cPanel
Disarankan menggunakan struktur aman Laravel:
```text
/home/username/
├── ppdb-core/              <-- Seluruh folder proyek Laravel (app, bootstrap, config, database, routes, storage, vendor, .env)
└── public_html/            <-- Isi dari folder 'public' proyek Laravel (index.php, .htaccess, favicon, css, js, logo, uploads)
```

> **Catatan `index.php`**: Jika folder dipisah seperti di atas, sesuaikan path di `public_html/index.php`:
> ```php
> require __DIR__.'/../ppdb-core/vendor/autoload.php';
> $app = require_once __DIR__.'/../ppdb-core/bootstrap/app.php';
> ```

### 2. Konfigurasi File `.env` di cPanel
Buat database & user MySQL melalui menu **MySQL Databases** di cPanel, lalu sesuaikan file `.env`:
```env
APP_NAME="PPDB SMPS 2 Al-Muhajirin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomain-anda.sch.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_ppdb
DB_USERNAME=username_ppdbuser
DB_PASSWORD=PasswordDatabaseKuat123!
```

### 3. Eksekusi Perintah Artisan via Browser (Deployment Helper Routes)
Karena server cPanel umumnya tidak memiliki terminal SSH, aplikasi ini telah dilengkapi rute khusus yang dapat diakses langsung melalui browser:

| Langkah | URL Endpoint yang Dibuka di Browser | Keterangan |
|:--:|:---|:---|
| **1** | `https://namadomain.com/generate-key` | Menjalankan `key:generate --force` untuk membuat Application Key di file `.env`. |
| **2** | `https://namadomain.com/migrate-seed` | Menjalankan `migrate --force` dan `db:seed --force` untuk membuat tabel dan akun admin default. |
| **3** | `https://namadomain.com/storage-link` | Menjalankan `storage:link` agar berkas yang diunggah dapat diakses publik. |
| **4** | `https://namadomain.com/clear-cache` | Menjalankan `optimize:clear` untuk membersihkan cache konfigurasi dan rute. |

Setiap endpoint di atas akan menampilkan status **SUCCESS** berwarna hijau di layar browser Anda.

---

## 🔑 Kredensial Akun Default Admin

Setelah perintah `migrate-seed` berhasil dijalankan, Anda dapat login ke portal admin di `https://namadomain.com/admin/login` menggunakan salah satu akun berikut:

| No | Username | Email | Password Default | Role |
|:--:|:---|:---|:---:|:---|
| 1 | `admin` | `admin@almuhajirin.sch.id` | `admin123` | Administrator Utama |
| 2 | `adminppdb` | `admin@ppdb.sch.id` | `admin123` | Administrator PPDB |
| 3 | `panitia` | `panitia@almuhajirin.sch.id` | `panitia123` | Panitia Admisi |

> [!WARNING]
> Sangat disarankan untuk segera mengganti password akun admin default setelah berhasil login ke panel admin demi keamanan sistem.

---

## 💳 Informasi Rekening Pembayaran Resmi

Pendaftar yang memilih metode **Transfer Bank** diinstruksikan melakukan transfer ke rekening resmi:
- **Bank**: Bank Mandiri
- **Nomor Rekening**: `1730004960275`
- **Atas Nama**: `PUTRI MUNAWWAROH`

---

## 🤖 Notifikasi Telegram Panitia (Opsional)

Untuk mengaktifkan pengiriman notifikasi instan ke grup WhatsApp/Telegram panitia saat ada berkas bukti pembayaran baru yang masuk, isi variabel berikut di file `.env`:
```env
TELEGRAM_BOT_TOKEN=123456789:AAFxxxxxxxxxxxxxxxxxxxxxx
TELEGRAM_PANITIA_CHAT_ID=-100xxxxxxxxxx
```

---

## 🔄 Perintah Background Task & Sinkronisasi API

Untuk menyinkronkan data pendaftar lokal ke FastAPI backend secara terjadwal:
```bash
# Sinkronkan data pending ke backend
php artisan ppdb:sync-to-api

# Mode dry-run (hanya melihat antrean tanpa mengirim)
php artisan ppdb:sync-to-api --dry-run
```
Pada cPanel, Anda dapat memasukkan perintah ini ke menu **Cron Jobs**:
```bash
/usr/local/bin/php /home/username/ppdb-core/artisan ppdb:sync-to-api >> /dev/null 2>&1
```

---

## 📄 Hak Cipta & Lisensi

Hak Cipta &copy; 2026–2028 **SMPS 2 Al-Muhajirin Purwakarta**. Seluruh hak cipta dilindungi undang-undang.
