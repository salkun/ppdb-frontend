# Rencana Fitur Ujian PPDB

## Tujuan dan aturan

- [x] Gunakan akun PPDB yang sudah ada; tidak membuat akun/password ujian baru.
- [x] Syarat akses: formulir lengkap, empat berkas wajib tercatat, dan pembayaran berstatus `paid`.
- [x] Reguler/Tahfidz: Tes Umum; Bahasa: Tes Umum + Bahasa; ICT: Tes Umum + ICT.
- [x] Soal pilihan ganda A-D dapat diimpor dari Word.
- [x] Jawaban disimpan otomatis dan antrean lokal dicoba kirim ulang saat koneksi pulih.
- [x] Timer dihitung dari batas waktu server; putus koneksi bukan pelanggaran.
- [x] Portal peserta dibatasi ke fitur ujian selama jadwal aktif atau sesi belum selesai.
- [x] Halaman yang ditinggalkan menangguhkan sesi; pelanggaran keempat menghentikan ujian.
- [x] Nilai siswa hanya terlihat setelah admin merilis hasil.

## Checklist implementasi

### 1. Kelayakan dan akses

- [x] Petakan struktur formulir, berkas, pembayaran, akun, dan jurusan yang sudah ada.
- [x] Terapkan satu pemeriksaan kelayakan di halaman peserta dan endpoint mulai ujian.
- [x] Periksa field wajib formulir siswa, alamat, kontak, dan data orang tua/wali.
- [x] Periksa path empat berkas wajib (KK, Akta, bukti NISN, pas foto).
- [x] Periksa pembayaran berstatus `paid` dan tampilkan persyaratan yang kurang.
- [x] Batasi jenis ujian berdasarkan jurusan di server.
- [ ] Verifikasi keberadaan fisik setiap berkas saat membuka akses ujian; pemeriksaan saat ini memeriksa path yang tercatat.

### 2. Pengelolaan admin

- [x] Sediakan konfigurasi Tes Umum, Tes Bahasa, dan Tes ICT: judul, petunjuk, jadwal, durasi, publikasi, dan rilis hasil.
- [x] Wajibkan jadwal mulai/selesai saat ujian dipublikasikan.
- [x] Pisahkan bank soal berdasarkan jenis ujian.
- [x] Sediakan template `.docx` dengan tabel Nomor, Pertanyaan, A-D, dan Kunci.
- [x] Validasi urutan nomor, isi soal/pilihan/kunci, dan soal duplikat saat impor.
- [x] Tampilkan pratinjau dan minta konfirmasi sebelum mengganti bank soal.
- [x] Cegah penggantian soal setelah peserta mulai ujian.
- [x] Terapkan pengacakan soal/pilihan dan penilaian server dengan nilai maksimum 100.
- [x] Tampilkan progres, jawaban, nilai, dan pelanggaran kepada admin.
- [x] Sediakan tindakan admin untuk membuka kembali ujian yang dihentikan dengan durasi penuh; jawaban dan riwayat pelanggaran dipertahankan.
- [ ] Batas percobaan belum dapat dikonfigurasi: implementasi saat ini menetapkan satu attempt per jenis ujian/peserta.
- [ ] Import rilis pertama hanya mendukung teks dalam tabel `.docx`; belum mendukung gambar atau soal kompleks.

### 3. Dashboard dan jadwal peserta

- [x] Tampilkan ujian yang cocok dengan jurusan, persyaratan, jadwal, durasi, dan status.
- [x] Kunci mulai sebelum persyaratan lengkap, di luar jadwal, atau sebelum soal dipublikasikan.
- [x] Batasi rute portal lain melalui middleware server selama jadwal atau attempt aktif.
- [x] Tampilkan peringatan aturan keluar halaman dan keterbatasan deteksi browser sebelum ujian.

### 4. Pelaksanaan dan ketahanan

- [x] Buat satu attempt unik per peserta dan jenis ujian; cegah attempt ganda.
- [x] Tetapkan waktu mulai dan batas waktu dari server, dibatasi oleh akhir jadwal.
- [x] Simpan jawaban saat berubah, lakukan percobaan ulang berkala, dan simpan antrean yang belum terkirim di browser.
- [x] Pulihkan jawaban lokal yang belum terkirim dan jawaban server saat halaman ujian dimuat kembali.
- [x] Jalankan timer berdasarkan waktu server; akhiri attempt dan hitung nilai di server.
- [x] Jangan kirim kunci jawaban ke halaman peserta.
- [ ] Audit perubahan setiap jawaban belum disimpan sebagai riwayat; yang dicatat saat ini adalah status dan pelanggaran.
- [ ] Uji konflik jawaban jika satu peserta membuka sesi yang sama di beberapa tab/perangkat.

### 5. Suspend dan pelanggaran

- [x] Deteksi perpindahan halaman/tab melalui `visibilitychange`; jaringan terputus tidak dihitung.
- [x] Catat waktu dan jumlah pelanggaran pada server, suspend pada pelanggaran 1-3, dan terminate pada pelanggaran ke-4.
- [x] Izinkan melanjutkan attempt yang sama selama belum melewati batas waktu.
- [x] Admin dapat meninjau dan membuka kembali attempt yang dihentikan.
- [ ] Deteksi keluar tidak dapat menjamin semua cara menutup halaman, berpindah aplikasi, atau memakai perangkat lain; mode layar penuh belum diwajibkan.

### 6. Hasil dan keamanan

- [x] Rilis hasil dikendalikan admin; peserta tidak menerima kunci saat mengerjakan.
- [x] Endpoint peserta memeriksa kepemilikan attempt, jurusan, persyaratan, status, dan waktu.
- [x] Catat keterbatasan deteksi browser di antarmuka peserta.
- [ ] Tetapkan prosedur operasional dan panduan panitia/peserta untuk hari ujian.

### 7. Validasi dan peluncuran

- [x] Uji pemetaan jurusan, akses, impor DOCX valid/duplikat, penyimpanan jawaban, pelanggaran, rilis nilai, dan pembukaan kembali oleh admin.
- [ ] Uji format Word yang salah, berkas/koneksi putus, timer kedaluwarsa, akses lintas akun, serta dua tab bersamaan.
- [ ] Uji tampilan pada browser dan perangkat yang akan digunakan.
- [ ] Dokumentasikan jadwal operasional, dukungan teknis, dan penanganan insiden.

## Catatan batasan dan kebijakan

Browser web tidak dapat sepenuhnya mencegah peserta menutup tab, berpindah aplikasi, memakai perangkat lain, atau kehilangan koneksi. Sistem mendeteksi sebagian perpindahan halaman, menyimpan jawaban di server dan antrean browser, serta tetap menjalankan timer server. Deteksi dapat keliru dan bukan bukti sempurna kecurangan.

Konfigurasi jadwal dan durasi dapat berbeda per jenis tes. Hasil yang dirilis saat ini berupa nilai; rincian jawaban hanya tersedia untuk admin. Importer tidak mendukung gambar dalam soal. Kebijakan pemulihan saat terjadi deteksi keliru adalah peninjauan admin dan pembukaan kembali dengan durasi penuh.
