<?php

namespace Tests\Unit;

use App\Models\ImportedDssdData;
use App\Services\DssdMirrorWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DssdMirrorWriterTest extends TestCase
{
    use RefreshDatabase;

    protected DssdMirrorWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->writer = new DssdMirrorWriter;
    }

    public function test_it_writes_and_deletes_opd_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '35.07.01.001',
            'uraian_dssd' => 'DSSD OPD Test',
            'produsen_data' => 'Dinas Kesehatan',
            'jenis_data' => 'OPD',
            'jenis_produsen' => 'Dinas',
            'tahun' => 2026,
            'satuan' => 'Dokumen',
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('dssd_opd', [
            'kode_dssd' => '35.07.01.001',
            'uraian_dssd' => 'DSSD OPD Test',
            'produsen_data' => 'Dinas Kesehatan',
            'jenis_produsen' => 'Dinas',
        ]);

        $this->writer->delete($item);

        $this->assertDatabaseMissing('dssd_opd', [
            'kode_dssd' => '35.07.01.001',
        ]);
    }

    public function test_it_applies_fallback_produsen_for_opd_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '35.07.01.002',
            'uraian_dssd' => 'DSSD OPD Fallback Test',
            'produsen_data' => null,
            'jenis_data' => 'OPD',
            'jenis_produsen' => null,
            'tahun' => 2026,
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('dssd_opd', [
            'kode_dssd' => '35.07.01.002',
            'produsen_data' => '[Tanpa Produsen Data]',
            'jenis_produsen' => null,
        ]);
    }

    public function test_it_writes_and_deletes_kecamatan_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '7.01.000102',
            'uraian_dssd' => 'DSSD Kecamatan Test',
            'produsen_data' => 'Kecamatan Kepanjen',
            'jenis_data' => 'Kecamatan',
            'tahun' => 2026,
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '7.01.000102',
            'nama_kecamatan' => 'Kecamatan Kepanjen',
            'produsen_data' => 'Kecamatan Kepanjen',
        ]);

        $this->writer->delete($item);

        $this->assertDatabaseMissing('kecamatan', [
            'kode_kecamatan' => '7.01.000102',
        ]);
    }

    public function test_it_preserves_null_for_kecamatan_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '7.01.000103',
            'uraian_dssd' => 'DSSD Kecamatan Null Test',
            'produsen_data' => null,
            'jenis_data' => 'Kecamatan',
            'jenis_produsen' => null,
            'tahun' => 2026,
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '7.01.000103',
            'nama_kecamatan' => '[Tanpa Produsen Data]',
            'produsen_data' => null,
            'jenis_produsen' => null,
        ]);
    }

    public function test_it_writes_and_deletes_kelurahan_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '35.07.25.1010.13',
            'uraian_dssd' => 'DSSD Kelurahan Test',
            'produsen_data' => 'Kelurahan Lawang',
            'jenis_data' => 'Kelurahan',
            'tahun' => 2026,
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('kelurahan', [
            'kode_kelurahan' => '35.07.25.1010.13',
            'nama_kelurahan' => 'Kelurahan Lawang',
            'produsen_data' => 'Kelurahan Lawang',
        ]);

        $this->writer->delete($item);

        $this->assertDatabaseMissing('kelurahan', [
            'kode_kelurahan' => '35.07.25.1010.13',
        ]);
    }

    public function test_it_preserves_null_for_kelurahan_mirror(): void
    {
        $item = new ImportedDssdData([
            'kode_dssd' => '35.07.25.1010.14',
            'uraian_dssd' => 'DSSD Kelurahan Null Test',
            'produsen_data' => null,
            'jenis_data' => 'Kelurahan',
            'jenis_produsen' => null,
            'tahun' => 2026,
        ]);

        $this->writer->write($item);

        $this->assertDatabaseHas('kelurahan', [
            'kode_kelurahan' => '35.07.25.1010.14',
            'nama_kelurahan' => '[Tanpa Produsen Data]',
            'produsen_data' => null,
            'jenis_produsen' => null,
        ]);
    }
}
