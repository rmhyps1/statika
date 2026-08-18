<?php

use App\Http\Controllers\ApiSyncController;
use App\Http\Controllers\DssdController;
use App\Http\Controllers\DssdKamasutaCompareController;
use App\Http\Controllers\ImportedDssdDataController;
use App\Http\Controllers\KamasutaController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');
Route::get('/dssd', [DssdController::class, 'index'])->name('dssd');
Route::post('/dssd/compare-kamasuta', [DssdKamasutaCompareController::class, 'compare'])->name('dssd.compare-kamasuta');
Route::get('/dssd/template', [ImportedDssdDataController::class, 'template'])->name('dssd.template');

Route::get('/kamasuta', [KamasutaController::class, 'judulList'])->name('kamasuta');
Route::get('/kamasuta/judul-detail', [KamasutaController::class, 'judulDetail'])->name('kamasuta.judul-detail');
Route::post('/kamasuta/sync', [ApiSyncController::class, 'syncKamasuta'])->name('kamasuta.sync');
Route::get('/api-sync/status/{jobId}', [ApiSyncController::class, 'checkSyncStatus'])->name('api-sync.status');

Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::post('/laporan/spasial', [LaporanController::class, 'updateSpasial'])->name('laporan.spasial.update');
Route::post('/laporan/spasial/manual', [LaporanController::class, 'storeSpasialManual'])->name('laporan.spasial.manual');

Route::post('/laporan/download/persentase', [LaporanController::class, 'downloadDocx'])->name('laporan.download');
Route::post('/laporan/download/template-dilaporkan', [LaporanController::class, 'downloadTemplateDilaporkan'])->name('laporan.download.template.dilaporkan');
Route::post('/laporan/download/template-disepakati', [LaporanController::class, 'downloadTemplateDisepakati'])->name('laporan.download.template.disepakati');
Route::post('/laporan/download/hasil-dilaporkan', [LaporanController::class, 'downloadDilaporkanDocx'])->name('laporan.download.dilaporkan');
Route::post('/laporan/download/hasil-disepakati', [LaporanController::class, 'downloadDisepakatiDocx'])->name('laporan.download.disepakati');

Route::post('imported-dssd-data/import', [ImportedDssdDataController::class, 'import'])->name('imported-dssd-data.import');
Route::post('imported-dssd-data', [ImportedDssdDataController::class, 'store'])->name('imported-dssd-data.store');
Route::post('api-sync/kamasuta', [ApiSyncController::class, 'syncKamasuta'])->name('api-sync.kamasuta');
Route::get('api-sync/status/{jobId}', [ApiSyncController::class, 'checkSyncStatus'])->name('api-sync.status');
Route::get('imported-dssd-data/export', [ImportedDssdDataController::class, 'export'])->name('imported-dssd-data.export');
Route::delete('imported-dssd-data/destroy-all', [ImportedDssdDataController::class, 'destroyAll'])->name('imported-dssd-data.destroy-all');
Route::patch('imported-dssd-data/{importedDssdData}/availability', [ImportedDssdDataController::class, 'updateAvailability'])->name('imported-dssd-data.availability');
Route::put('imported-dssd-data/{importedDssdData}', [ImportedDssdDataController::class, 'update'])->name('imported-dssd-data.update');
Route::delete('imported-dssd-data/{importedDssdData}', [ImportedDssdDataController::class, 'destroy'])->name('imported-dssd-data.destroy');
