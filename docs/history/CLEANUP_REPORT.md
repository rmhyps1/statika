# Cleanup Report — 2026-07-22

## Summary

Deep dive cleanup of Laravel project `pklmagang`. Three categories of work: dead code removal, validation refactoring, and test coverage expansion.

---

## 1. Dead Code Removal

**Status:** Already clean. Target files (`check_db2.php`, `temp_api.json`, `temp.txt`, `test_table.php`) were not present in project root — previously removed.

**routes/api.php:** Already clean — contains only `<?php` and `use Illuminate\Support\Facades\Route;`. No Sanctum route found.

---

## 2. FormRequest Extraction

Extracted inline validation from two complex controllers into 7 FormRequest classes.

### Files Created

| File | Purpose |
|------|---------|
| `app/Http/Requests/StoreLaporanRequest.php` | Validate laporan creation (nama, jumlah_disepakati, jumlah_dilaporkan) |
| `app/Http/Requests/UpdateLaporanRequest.php` | Validate laporan updates (same rules as store) |
| `app/Http/Requests/ResetLaporanRequest.php` | Validate reset target (disepakati/dilaporkan/semua) |
| `app/Http/Requests/UploadLaporanRequest.php` | Validate document upload (jenis_laporan + file) |
| `app/Http/Requests/StoreImportedDssdRequest.php` | Validate manual DSSD entry (12 fields) |
| `app/Http/Requests/UpdateImportedDssdRequest.php` | Validate DSSD update (same 12 fields) |
| `app/Http/Requests/ImportDssdFileRequest.php` | Validate DSSD file import (CSV/Excel array) |

### Controllers Modified

| File | Change |
|------|--------|
| `app/Http/Controllers/LaporanController.php` | `store()`, `reset()`, `update()`, `uploadCombined()` now use FormRequest type-hints. Uses `$request->validated()` instead of `$request->all()` for mass assignment safety. |
| `app/Http/Controllers/ImportedDssdDataController.php` | `store()`, `update()`, `import()` now use FormRequest type-hints. Uses `$request->validated()`. |

### Controllers NOT Modified (by design)

| File | Reason |
|------|--------|
| `KecamatanController.php` | Simple CRUD, validation already well-encapsulated in private `validated()` method |
| `KelurahanController.php` | Same pattern as Kecamatan — clean enough |
| `DssdOpdController.php` | Same pattern — private `validated()` method handles unique ignore correctly |
| `DssdKamasutaCompareController.php` | Single inline validation rule, not worth extracting |
| `KamasutaController.php` | No validation — reads from external API |
| `ApiSyncController.php` | Minimal validation |
| `DssdController.php` | Read-only, no validation |

---

## 3. Model Factories

Created factories for the 3 models that lacked them.

| File | Model | Notes |
|------|-------|-------|
| `database/factories/ProdusenDataFactory.php` | `ProdusenData` | Random nama, jumlah_disepakati, jumlah_dilaporkan |
| `database/factories/ImportedDssdDataFactory.php` | `ImportedDssdData` | Sektoral-style kode_dssd, states: `available()`, `unavailable()` |
| `database/factories/DssdOpdFactory.php` | `DssdOpd` | Random OPD-style kode, nullable fields |

Pre-existing: `UserFactory.php` (untouched).

---

## 4. Feature Tests

Created 2 comprehensive feature test files covering the previously untested critical flows.

### `tests/Feature/LaporanTest.php` — 16 test cases

| Test | What it verifies |
|------|-----------------|
| `test_laporan_index_returns_ok` | GET /laporan returns 200 |
| `test_laporan_index_shows_produsen_data` | Data visible in view |
| `test_laporan_index_filters_by_tahun` | Tahun query param accepted |
| `test_laporan_store_creates_record` | POST /laporan creates DB record + redirect |
| `test_laporan_store_validates_required_fields` | Missing fields → validation errors |
| `test_laporan_store_rejects_negative_numbers` | Negative values rejected |
| `test_laporan_store_rejects_non_integer` | String/float rejected |
| `test_laporan_update_modifies_record` | PUT updates record correctly |
| `test_laporan_update_validates_required_fields` | Missing fields → errors |
| `test_laporan_destroy_deletes_record` | DELETE removes from DB |
| `test_laporan_reset_disepakati` | Reset only disepakati column |
| `test_laporan_reset_dilaporkan` | Reset only dilaporkan column |
| `test_laporan_reset_semua` | Reset both columns |
| `test_laporan_reset_validates_target` | Invalid target rejected |
| `test_laporan_reset_requires_target` | Missing target rejected |
| `test_laporan_upload_rejects_missing_file` | No file → validation error |
| `test_laporan_upload_rejects_invalid_jenis` | Bad jenis_laporan → error |

### `tests/Feature/DssdTest.php` — 22 test cases

| Test | What it verifies |
|------|-----------------|
| `test_dssd_index_returns_ok` | GET /dssd returns 200 |
| `test_dssd_index_shows_imported_data` | Data visible in view |
| `test_dssd_index_shows_stats` | Stats computed without error |
| `test_dssd_opd_store_creates_record` | POST creates DssdOpd |
| `test_dssd_opd_store_validates_required` | Missing fields rejected |
| `test_dssd_opd_store_rejects_duplicate_kode` | Unique constraint enforced |
| `test_dssd_opd_update_modifies_record` | PUT updates DssdOpd |
| `test_dssd_opd_update_allows_same_kode_for_same_record` | Unique-ignore-self works |
| `test_dssd_opd_destroy_deletes_record` | DELETE removes record |
| `test_imported_dssd_store_creates_record` | POST creates ImportedDssdData |
| `test_imported_dssd_store_validates_required` | Required fields enforced |
| `test_imported_dssd_store_validates_ketersediaan_enum` | Only ada/tidak accepted |
| `test_imported_dssd_store_validates_tahun_range` | Year < 1900 rejected |
| `test_imported_dssd_update_modifies_record` | PUT updates + sets ketersediaan_source=manual |
| `test_imported_dssd_update_availability` | PATCH toggles ketersediaan_data |
| `test_imported_dssd_update_availability_rejects_invalid` | Bad enum rejected |
| `test_imported_dssd_destroy_deletes_record` | DELETE removes single record |
| `test_imported_dssd_destroy_all_truncates_table` | DELETE all truncates table |
| `test_imported_dssd_import_rejects_missing_file` | No file → error |
| `test_dssd_compare_validates_tahun` | Missing tahun → error |
| `test_dssd_compare_rejects_invalid_tahun_range` | Out-of-range tahun rejected |
| `test_dssd_index_filters_by_jenis_data` | Filter by jenis_data works |
| `test_dssd_index_filters_by_tahun` | Filter by tahun works |
| `test_dssd_index_filters_by_search` | Keyword search works |

### Pre-existing Tests (untouched)

- `tests/Feature/ExampleTest.php`
- `tests/Feature/KamasutaTest.php` (3 tests)
- `tests/Unit/DocumentParserServiceTest.php`
- `tests/Unit/SyncKamasutaJobTest.php`
- `tests/Unit/ReportGeneratorServiceTest.php`
- `tests/Unit/ExampleTest.php`

---

## File Inventory

### New Files (17)

```
app/Http/Requests/StoreLaporanRequest.php
app/Http/Requests/UpdateLaporanRequest.php
app/Http/Requests/ResetLaporanRequest.php
app/Http/Requests/UploadLaporanRequest.php
app/Http/Requests/StoreImportedDssdRequest.php
app/Http/Requests/UpdateImportedDssdRequest.php
app/Http/Requests/ImportDssdFileRequest.php
database/factories/ProdusenDataFactory.php
database/factories/ImportedDssdDataFactory.php
database/factories/DssdOpdFactory.php
tests/Feature/LaporanTest.php
tests/Feature/DssdTest.php
```

### Modified Files (3)

```
app/Http/Controllers/LaporanController.php
app/Http/Controllers/ImportedDssdDataController.php
CHANGELOG.md
```

### Deleted Files

None (targets were already absent).

---

## Notes

- **No `php artisan` used.** All files written manually.
- **No composer/npm commands run.** No new dependencies.
- `$request->all()` replaced with `$request->validated()` in refactored controllers — prevents mass-assignment of unexpected fields.
- Simple controllers (Kecamatan, Kelurahan, DssdOpd) intentionally left as-is — their private `validated()` methods are already clean and well-structured.
- Tests use `RefreshDatabase` trait with SQLite in-memory (as configured in `phpunit.xml`).
- `uploadCombined()` in LaporanController: removed the redundant `ValidationException` catch block since FormRequest handles validation before the method is called.
