<?php

namespace Tests\Feature;

use App\Services\DssdImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MultiSheetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_service_processes_ewalidata_and_sektoral_and_skips_spasial(): void
    {
        $spreadsheet = new Spreadsheet;

        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('e-walidata');
        $sheet1->fromArray([
            ['No', 'Kode DSSD', 'Uraian DSSD', 'Satuan', 'Definisi Operasional', 'Tag urusan', 'Produsen Data'],
            ['1', '5.03.000001', 'Dokumen Pengadaan ASN', 'Dokumen', 'Definisi ASN', '5.03 Kepegawaian', 'BKPSDM'],
            ['2', '7.01.000102', 'Laporan Koordinasi', 'Laporan', 'Definisi Koordinasi', '7.01 Kecamatan', 'Kecamatan Se-Kabupaten Malang'],
        ]);

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('sektoral');
        $sheet2->fromArray([
            ['No', 'Kode Data', 'Uraian DSSD', 'Satuan', 'Definisi Operasional', 'Tag urusan', 'Produsen Data', 'Info Sub Kegiatan'],
            ['1', '35.07.405.001', 'Banyaknya ASN Golongan', 'Orang', 'Definisi Golongan', 'Pemerintahan', 'BKPSDM', ''],
            ['2', '35.07.01.001.01', 'Jumlah Penduduk Kecamatan', 'Orang', 'Definisi Penduduk', 'Kecamatan', 'Kecamatan Kepanjen', ''],
            ['3', '35.07.25.1010.13', 'Luas Lahan Hunian', 'Ha', 'Definisi Lahan', 'Kelurahan', 'Kelurahan Lawang', ''],
        ]);

        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('spasial');
        $sheet3->fromArray([
            ['No', 'Kode Data', 'Nama Informasi Geospasial', 'Format penyimpanan data', 'Skala', 'Produsen Data'],
            ['1', 'DS.35.07.407.001', 'Peta Bahaya Banjir', 'Shp', '1:25000', 'BPBD'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $service = app(DssdImportService::class);
        $result = $service->processFile($tempPath, 'xlsx', 'test.xlsx', 2026, null);

        @unlink($tempPath);

        $this->assertEquals(5, $result['count']);

        $this->assertDatabaseHas('imported_dssd_data', [
            'kode_dssd' => '5.03.000001',
            'jenis_data' => 'OPD',
        ]);
        $this->assertDatabaseHas('dssd_opd', [
            'kode_dssd' => '5.03.000001',
        ]);

        $this->assertDatabaseHas('imported_dssd_data', [
            'kode_dssd' => '7.01.000102',
            'jenis_data' => 'Kecamatan',
        ]);
        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '7.01.000102',
            'nama_kecamatan' => 'Kecamatan Se-Kabupaten Malang',
        ]);

        $this->assertDatabaseHas('imported_dssd_data', [
            'kode_dssd' => '35.07.01.001.01',
            'jenis_data' => 'Kecamatan',
        ]);
        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '35.07.01.001.01',
            'nama_kecamatan' => 'Kecamatan Kepanjen',
        ]);

        $this->assertDatabaseHas('imported_dssd_data', [
            'kode_dssd' => '35.07.25.1010.13',
            'jenis_data' => 'Kelurahan',
        ]);
        $this->assertDatabaseHas('kelurahan', [
            'kode_kelurahan' => '35.07.25.1010.13',
            'nama_kelurahan' => 'Kelurahan Lawang',
        ]);

        $this->assertDatabaseMissing('imported_dssd_data', [
            'kode_dssd' => 'DS.35.07.407.001',
        ]);
    }
}
