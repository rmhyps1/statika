# Domain Glossary & Context

## Laporan Module

### LaporanStats
Deep module (`App\Services\LaporanStats`) responsible for computing `/laporan` statistics for a given year.
- **Data sources:** `imported_dssd_data` and `data_spasial_manual`.
- **Interface:** `stats(?string $tahun = null): array`
- **Output:** Returns a report stats structure containing `produsenData` (grouped items), `totalDisepakati`, `totalDilaporkan`, `totalKamasuta`, `totalSpasial`, `total`, and `persentase`.
- **Grouping Rules:**
  - Items matching `jenis_data = 'Kecamatan'` and `kode_dssd LIKE '7.01%'` map to `'Seluruh Kecamatan Se-Kabupaten Malang'`.
  - Items with `null` or empty `produsen_data` map to `'[Tanpa Produsen Data]'`.

## DSSD Module

### DssdMirrorWriter
Deep module (`App\Services\DssdMirrorWriter`) responsible for synchronizing data to dedicated mirror tables (`dssd_opd`, `kecamatan`, `kelurahan`) based on `jenis_data`.
- **Interface:**
  - `write(ImportedDssdData $item): void`
  - `delete(ImportedDssdData $item): void`
- **Target Mirror Tables:**
  - `OPD` -> `dssd_opd` (by `kode_dssd`)
  - `Kecamatan` -> `kecamatan` (by `kode_kecamatan`)
  - `Kelurahan` -> `kelurahan` (by `kode_kelurahan`, creates parent `Kecamatan` if missing)

## Architecture Cleanup (C5)

- Removed orphaned controllers (`DssdOpdController`, `KecamatanController`, `KelurahanController`) and dead routes, centralizing all mirror persistence in `DssdMirrorWriter`.
- Removed legacy document parsing (`DocumentParserService`, `DocumentParserServiceTest`, `*LaporanRequest` FormRequests, `ProdusenData` model, `ProdusenDataSeeder`, `config/instansi.php`).
