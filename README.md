# Statika - Laravel Application

Aplikasi sistem pengelolaan, visualisasi, dan sinkronisasi data DSSD (Daftar Standar Data) serta Kamasuta API Kabupaten Malang berbasis Laravel 13.

---

## Fitur Utama Aplikasi

1. **Pengelolaan Data DSSD**: Visualisasi, komparasi, serta pencarian data DSSD (baik spasial maupun non-spasial).
2. **Integrasi & Sinkronisasi Kamasuta API**: Penarikan otomatis data Indikator, Kegiatan, Variabel, dan OPD dari API Kamasuta Kabupaten Malang (https://kamasuta.malangkab.go.id).
3. **Komparasi DSSD vs Kamasuta**: Pemetaan dan analisis kecocokan data DSSD dengan Kamasuta.
4. **Import & Export**: Fitur import data via CSV/Excel serta export hasil analisis/laporan ke dalam format Word (.docx) berdasarkan template dokumen resmi.
5. **DssdMirrorWriter**: Mekanisme sinkronisasi data ke tabel mirror (dssd_opd, kecamatan, kelurahan) berdasarkan jenis data DSSD yang diimport.

---

## Prasyarat Sistem & Konfigurasi PHP

Sebelum menginstal aplikasi secara manual, pastikan sistem Anda memenuhi spesifikasi berikut:
- PHP >= 8.3
- Composer >= 2.0
- Node.js >= 18 & NPM
- MySQL >= 8.0 atau MariaDB

### Ekstensi PHP Wajib
- zip (untuk pengolahan dokumen ZIP / Office)
- xml / dom (untuk dokumen XML / Word / Excel)
- gd (untuk manipulasi gambar & dokumen Excel)
- curl & mbstring (untuk komunikasi API Kamasuta)
- pdo_mysql (untuk database MySQL)
- fileinfo (untuk deteksi MIME-type berkas)

---

## Konfigurasi Ekstensi PHP per Sistem Operasi

### Windows (Laragon)
1. Buka aplikasi Laragon.
2. Klik kanan pada area mana saja di jendela Laragon.
3. Pilih menu PHP -> Extensions.
4. Centang ekstensi berikut:
   - zip
   - gd
   - xml
   - pdo_mysql
   - curl
   - mbstring
   - fileinfo
5. Restart service Laragon (Menu -> Reload / Restart All).

### Windows (XAMPP / Manual php.ini)
1. Buka file php.ini di folder PHP Anda (misal C:\xampp\php\php.ini).
2. Cari dan hilangkan tanda titik koma (;) pada baris berikut untuk mengaktifkannya:
   ```ini
   extension=zip
   extension=gd
   extension=fileinfo
   extension=pdo_mysql
   extension=curl
   extension=mbstring
   extension=openssl
   ```
3. Simpan file.
4. Restart service Apache pada XAMPP Control Panel.

### Linux (Ubuntu / Debian / VPS)
Jalankan perintah berikut di terminal untuk memperbarui package list dan memasang seluruh ekstensi yang dibutuhkan:
```bash
sudo apt update
sudo apt install -y php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-curl php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-intl
```

### macOS (Homebrew Setup)
1. Pasang PHP 8.3 menggunakan Homebrew jika belum terinstall:
   ```bash
   brew update
   brew install php@8.3 composer node mysql
   ```
2. Hubungkan PHP Homebrew ke system PATH Anda (ikuti instruksi keluaran brew).
3. Jalankan service MySQL bawaan brew:
   ```bash
   brew services start mysql
   ```
4. Ekstensi PHP seperti zip, gd, xml, curl, dan pdo_mysql biasanya sudah terpasang secara default pada instalasi PHP Homebrew.

---

## Panduan Menjalankan via Docker & Docker Compose (Direkomendasikan)

Jika Anda ingin menjalankan aplikasi tanpa perlu meng-install PHP, Composer, Node.js, atau MySQL secara manual di sistem lokal, Anda dapat menggunakan Docker.

### Layanan dalam Docker Compose
- **app**: Container PHP 8.3 CLI + Laravel 13 + Node.js (Vite asset build otomatis). Berjalan di port lokal 8181.
- **db**: Container MySQL 8.4 (Database server). Berjalan di port lokal 3306.
- **phpmyadmin**: phpMyAdmin untuk manajemen database berbasis GUI. Berjalan di port lokal 8182.

### Langkah Menjalankan Docker

1. Clone repository dan pindah ke direktori project:
   ```bash
   git clone https://github.com/rmhyps1/statika.git
   cd Statika
   ```

2. Salin berkas lingkungan (.env):
   ```bash
   cp .env.example .env
   ```

3. Jalankan Container Docker:
   ```bash
   docker compose up -d --build
   ```
   *Catatan: Proses build pertama kali akan mengunduh image, memasang dependensi Composer dan NPM, meng-compile aset Vite (npm run build), melakukan migrasi database, dan melakukan seeding data otomatis.*

4. Akses Layanan:
   - **Aplikasi Web**: http://localhost:8181
   - **phpMyAdmin**: http://localhost:8182 (Username: `statika`, Password: `password`)

5. Perintah Operasional Docker:
   - **Melihat Status Container**: `docker compose ps`
   - **Melihat Log Aplikasi**: `docker compose logs -f app`
   - **Masuk ke Terminal Container**: `docker compose exec app bash`
   - **Menghentikan Container**: `docker compose down`
   - **Menghentikan & Menghapus Volume Database**: `docker compose down -v`

---

## Panduan Instalasi Manual Lokal (Development)

Jika Anda ingin menjalankan project secara manual langsung pada sistem operasi Anda, ikuti langkah-langkah berikut:

### 1. Clone Repository & Masuk Direktori
```bash
git clone https://github.com/rmhyps1/statika.git
cd Statika
```

### 2. Setup File Environment (.env) & Kredensial Database
Salin file konfigurasi contoh menjadi .env:

- **Windows (CMD / PowerShell):**
  ```powershell
  copy .env.example .env
  ```
- **Linux / macOS:**
  ```bash
  cp .env.example .env
  ```

Buka file `.env` baru tersebut menggunakan text editor, dan sesuaikan kredensial koneksi database MySQL lokal Anda (username dan password):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=statika
DB_USERNAME=root
DB_PASSWORD=your_password
```

Jika ingin menggunakan integrasi Kamasuta API, lengkapi baris berikut:
```env
KAMASUTA_API_URL=https://kamasuta.malangkab.go.id
KAMASUTA_API_TOKEN=your_api_token_here
```

### 3. Konfigurasi Hak Akses Direktori (Khusus Linux / macOS)
Jika Anda menggunakan Linux atau macOS, jalankan perintah berikut agar web server dapat menulis ke folder storage dan cache:
```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### 4. Jalankan Setup Otomatis (Anti-Gagal)
Jalankan satu perintah berikut untuk menyelesaikan seluruh konfigurasi (instalasi dependency PHP, key generation, auto-create database jika belum ada, migrasi tabel beserta seeder data awal, instalasi NPM, dan build aset Vite):
```bash
composer run setup
```

### 5. Jalankan Server Development
Setelah proses setup selesai tanpa error, jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser di http://127.0.0.1:8000.

---

## Panduan Penggunaan Aplikasi (Workflow)

1. **Unggah Template DSSD**: Masuk ke menu DSSD, gunakan template Excel (`template/TEMPLATE DATA DSSD.xlsx`) untuk mengunggah daftar data spasial atau kecamatan.
2. **Sinkronisasi Data Kamasuta**: Jalankan sinkronisasi data dari dashboard Kamasuta untuk menarik data indikator dan kegiatan terbaru dari server Kamasuta.
3. **Analisis Laporan**: Halaman `/laporan` akan secara dinamis memetakan persentase keterisian data, memisahkan data berjenis Kecamatan, dan menangani item tanpa produsen data ke kategori '[Tanpa Produsen Data]'.
4. **Cetak Dokumen**: Klik tombol Cetak Laporan untuk melakukan ekspor ke Word (.docx) berbasis template otomatis.

---

## Penanganan Masalah Umum (Troubleshooting)

### 1. Proses Setup Gagal Saat Membuat Database (Access Denied)
- **Penyebab**: Username dan password MySQL di file `.env` salah, atau user tersebut tidak memiliki izin (privilege) untuk membuat database baru.
- **Solusi**: Pastikan `DB_USERNAME` dan `DB_PASSWORD` di `.env` sudah benar. Jika menggunakan root tanpa password, kosongkan bagian password. Jika user MySQL Anda dibatasi, buat database kosong bernama `statika` secara manual lewat phpMyAdmin/MySQL CLI, lalu jalankan kembali `composer run setup`.

### 2. Port is already allocated (Docker)
- **Penyebab**: Port 8181, 8182, atau 3306 sudah digunakan oleh service lain di komputer Anda.
- **Solusi**: Buka file `docker-compose.yml`, ubah mapping port eksternal (sisi kiri dari titik dua), misalnya `"8185:8000"` untuk container app, lalu jalankan kembali `docker compose up -d`.

### 3. Error ext-zip atau ext-gd missing
- **Penyebab**: Ekstensi PHP zip atau gd belum terinstall atau belum diaktifkan.
- **Solusi**: Ikuti panduan konfigurasi ekstensi PHP per OS di atas untuk mengaktifkan modul yang bersangkutan.
