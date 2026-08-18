# Dokumentasi Perbaikan (Changelog)

## 2026-07-22 09:30 WIB

### 10. Ekstrak FormRequest dari Controller
- **Lokasi:** `app/Http/Requests/`, `app/Http/Controllers/LaporanController.php`, `app/Http/Controllers/ImportedDssdDataController.php`
- **Masalah:** Validasi inline duplikat di LaporanController (store/update pakai rules identik) dan ImportedDssdDataController (store/update 12 field identik). Melanggar prinsip DRY dan menyulitkan maintenance.
- **Ganti:** Ekstrak 7 FormRequest class:
  - `StoreLaporanRequest` — validasi create laporan
  - `UpdateLaporanRequest` — validasi update laporan
  - `ResetLaporanRequest` — validasi target reset
  - `UploadLaporanRequest` — validasi upload file dokumen
  - `StoreImportedDssdRequest` — validasi create DSSD manual
  - `UpdateImportedDssdRequest` — validasi update DSSD
  - `ImportDssdFileRequest` — validasi import file CSV/Excel
- Controller sekarang pakai `$request->validated()` bukan `$request->all()`, lebih aman.

### 11. Penambahan Factory untuk Model
- **Lokasi:** `database/factories/`
- **Masalah:** Tidak ada factory untuk model `ProdusenData`, `ImportedDssdData`, dan `DssdOpd`. Testing terpaksa insert data manual.
- **Ganti:** Tambah 3 factory class: `ProdusenDataFactory`, `ImportedDssdDataFactory` (dengan state `available()`/`unavailable()`), `DssdOpdFactory`.

### 12. Feature Test untuk Laporan dan DSSD
- **Lokasi:** `tests/Feature/LaporanTest.php`, `tests/Feature/DssdTest.php`
- **Masalah:** Route kritis Laporan (CRUD + reset + upload) dan DSSD (CRUD + filter + compare) tidak punya test. Regresi bisa lolos tanpa terdeteksi.
- **Ganti:** Tambah 2 file feature test:
  - `LaporanTest` — 16 test case: index, store (validasi positif+negatif), update, destroy, reset (3 target + invalid), upload validasi
  - `DssdTest` — 22 test case: DSSD index + filter, DssdOpd CRUD + unique, ImportedDssd CRUD + availability + destroy-all, import validasi, compare validasi

## 2026-07-21 09:40 WIB

### 1. View DSSD Bloated & Crash (HTTP 500)
- **Lokasi:** `resources/views/dssd.blade.php`
- **Error:** File terlalu besar. Ekstrak modal manual sebelumnya memotong tag `div` dan merusak kutip `@include`. Web crash (HTTP 500).
- **Ganti:** Ekstrak 5 modal ke `resources/views/dssd-components/`. Gunakan regex PHP (via Docker) agar pemotongan tag HTML aman. File utama jadi pendek. Web kembali HTTP 200.

### 2. Document Parser (Baca DOCX Gagal)
- **Lokasi:** `app/Services/DocumentParserService.php`
- **Error:** Gagal ekstrak data dari DOCX karena kolom tabel di-hardcode (index 1). Format tabel berubah = kode gagal baca.
- **Ganti:** Loop semua kolom tabel secara dinamis. Cari nama instansi pakai regex tanpa patokan index. Parser PDF juga dibuat lebih toleran spasi/titik.

### 3. Generator DOCX Laporan Rusak
- **Lokasi:** `app/Services/ReportGeneratorService.php`
- **Error:** File Word hasil export korup saat dibuka di MS Word versi baru. Disebabkan manipulasi paksa `document.xml` menggunakan `ZipArchive` & `DOMDocument`.
- **Ganti:** Hapus manipulasi XML kotor. Murni pakai library bawaan `TemplateProcessor` untuk replace variabel.

### 4. Job Sync Kamasuta Mati Diam-diam
- **Lokasi:** `app/Jobs/SyncKamasutaJob.php`
- **Error:** Proses gagal tapi tidak tercatat (silent fail). Worker tidak tahu ada error.
- **Ganti:** Tambah `Log::error`. Lempar ulang exception (`throw`) agar error tertangkap worker Laravel.
### 5. Integrasi Job Kamasuta Palsu & Tangkap Error Bisu
- **Lokasi:** `app/Jobs/SyncKamasutaJob.php`
- **Error:** Job hanya berisi fungsi `sleep(3)` (pura-pura sinkronisasi). Selain itu, blok `catch` menangkap error tetapi mengubahnya menjadi response sukses semu bagi queue worker, sehingga error tidak pernah tercatat di *failed jobs* dan tidak ada *stack trace*.
- **Ganti:** 
  1. Menghapus `sleep()`.
  2. Mengimplementasikan integrasi HTTP client nyata (`Http::withToken()`) untuk menembak API Kamasuta.
  3. Menggunakan `DB::transaction()` untuk update data pencocokan `kode_dssd`.
  4. Menambahkan `throw $e;` di dalam blok catch setelah proses *logging*, sehingga job akan di-mark sebagai *failed* oleh Laravel Queue Worker jika benar-benar gagal.

### 6. Refactor Query N+1 di Job Sync Kamasuta
- **Lokasi:** `app/Jobs/SyncKamasutaJob.php`
- **Error:** Proses update data DSSD berada di dalam loop `foreach`. Jika ada 500 data dari Kamasuta, akan mengeksekusi 500 query UPDATE ke database (Query N+1 issue), menyebabkan beban server tinggi.
- **Ganti:** Mengumpulkan array berisi semua `kode_dssd` yang didapat dari respons API. Kemudian melakukan bulk update (hanya 1 buah query UPDATE dengan klausa `WHERE IN (...)`) pada tabel `dssd_data`.

### 7. Perbaikan Env Misterius Kamasuta API
- **Lokasi:** `.env.example`
- **Error:** Endpoint API dan variabel token untuk sinkronisasi Kamasuta dipakai di Job, tapi kurang terdokumentasi/lengkap di environment template.
- **Ganti:** Menambahkan `KAMASUTA_API_URL` ke `.env.example` melengkapi `KAMASUTA_API_TOKEN` yang sudah ada agar jelas bagi developer lain saat instalasi pertama kali.

### 8. Pengetatan Validasi File Upload DSSD
- **Lokasi:** `app/Http/Controllers/ImportedDssdDataController.php`
- **Error:** Validasi file upload sebelumnya hanya membatasi tipe MIME (`mimes:csv,txt,xlsx,xls`), yang bisa dikelabui dengan mengubah header tipe file palsu. Selain itu tidak ada batasan ukuran file, sehingga rentan serangan *Denial of Service* (DoS) melalui upload file raksasa.
- **Ganti:** Menambahkan validasi `max:10240` (maksimal 10MB per file) dan rule `extensions:csv,txt,xlsx,xls` bawaan Laravel 11 untuk memeriksa ekstensi asli secara ketat meskipun MIME di-bypass.

### 8.1 Revisi Batas Ukuran File Upload
- **Lokasi:** `app/Http/Controllers/ImportedDssdDataController.php`
- **Error:** Batasan 10MB dirasa kurang untuk ukuran data real-world yang bisa saja sangat besar.
- **Ganti:** Menaikkan batas ukuran upload maksimal (rule `max`) menjadi 40960 KB (40 MB).

### 9. Penambahan Unit Test Dasar
- **Lokasi:** `tests/Unit/DocumentParserServiceTest.php`, `tests/Unit/SyncKamasutaJobTest.php`, `tests/Unit/ReportGeneratorServiceTest.php`
- **Error:** Fitur krusial yang berhubungan dengan eksekusi file dan sinkronisasi API tidak memiliki unit test untuk menjaga keandalannya ketika dikembangkan lebih lanjut.
- **Ganti:** Menambahkan 3 class pengujian *Unit Test* untuk:
  1. `DocumentParserService`: Menguji penanganan ekstensi file asing dan *Exception handling* di parser library.
  2. `SyncKamasutaJob`: Menguji respon gagal integrasi (HTTP Failed) dan merespon tangkapan data kosong.
  3. `ReportGeneratorService`: Menguji penanganan absennya template laporan `template_persentase.docx`.
