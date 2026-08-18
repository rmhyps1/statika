<?php

namespace Tests\Feature;

use App\Models\DssdOpd;
use App\Models\ImportedDssdData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DssdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array']);
    }

    public function test_dssd_index_returns_ok(): void
    {
        $this->get(route('dssd'))->assertOk();
    }

    public function test_dssd_index_shows_imported_data(): void
    {
        ImportedDssdData::factory()->create(['uraian_dssd' => 'Jumlah Penduduk Miskin']);

        $this->get(route('dssd'))
            ->assertOk()
            ->assertSee('Jumlah Penduduk Miskin');
    }

    public function test_dssd_index_shows_stats(): void
    {
        ImportedDssdData::factory()->count(3)->create();

        $response = $this->get(route('dssd'));
        $response->assertOk();
    }

    public function test_imported_dssd_store_creates_record(): void
    {
        $data = [
            'kode_dssd' => '35.07.01.01.0001',
            'uraian_dssd' => 'Jumlah Penduduk',
            'ketersediaan_data' => 'ada',
            'jenis_data' => 'OPD',
        ];

        $this->post(route('imported-dssd-data.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('imported_dssd_data', ['kode_dssd' => '35.07.01.01.0001']);
        $this->assertDatabaseHas('dssd_opd', ['kode_dssd' => '35.07.01.01.0001']);
    }

    public function test_imported_dssd_store_syncs_kecamatan_and_kelurahan_mirrors(): void
    {
        $kecData = [
            'kode_dssd' => '7.01.000102',
            'uraian_dssd' => 'Manual Kecamatan',
            'produsen_data' => 'Kecamatan Singosari',
            'ketersediaan_data' => 'ada',
            'jenis_data' => 'Kecamatan',
        ];

        $this->post(route('imported-dssd-data.store'), $kecData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '7.01.000102',
            'nama_kecamatan' => 'Kecamatan Singosari',
        ]);

        $kelData = [
            'kode_dssd' => '35.07.25.1010.13',
            'uraian_dssd' => 'Manual Kelurahan',
            'produsen_data' => 'Kelurahan Lawang',
            'ketersediaan_data' => 'ada',
            'jenis_data' => 'Kelurahan',
        ];

        $this->post(route('imported-dssd-data.store'), $kelData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kelurahan', [
            'kode_kelurahan' => '35.07.25.1010.13',
            'nama_kelurahan' => 'Kelurahan Lawang',
        ]);
    }

    public function test_imported_dssd_store_validates_required(): void
    {
        $this->post(route('imported-dssd-data.store'), [])
            ->assertSessionHasErrors(['kode_dssd', 'uraian_dssd', 'ketersediaan_data']);
    }

    public function test_imported_dssd_store_validates_ketersediaan_enum(): void
    {
        $this->post(route('imported-dssd-data.store'), [
            'kode_dssd' => 'TEST',
            'uraian_dssd' => 'Test',
            'ketersediaan_data' => 'mungkin',
        ])->assertSessionHasErrors(['ketersediaan_data']);
    }

    public function test_imported_dssd_store_validates_tahun_range(): void
    {
        $this->post(route('imported-dssd-data.store'), [
            'kode_dssd' => 'TEST',
            'uraian_dssd' => 'Test',
            'ketersediaan_data' => 'ada',
            'tahun' => 1800,
        ])->assertSessionHasErrors(['tahun']);
    }

    public function test_imported_dssd_update_modifies_record(): void
    {
        $dssd = ImportedDssdData::factory()->create([
            'kode_dssd' => 'UPDATED-001',
            'jenis_data' => 'OPD',
            'tahun' => 2026,
            'produsen_data' => 'Dinas Lama',
        ]);

        $this->put(route('imported-dssd-data.update', $dssd), [
            'kode_dssd' => 'UPDATED-001',
            'uraian_dssd' => 'Updated uraian',
            'ketersediaan_data' => 'ada',
            'jenis_data' => 'OPD',
            'tahun' => 2026,
            'produsen_data' => 'Dinas Baru',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $dssd->refresh();
        $this->assertEquals('Updated uraian', $dssd->uraian_dssd);
        $this->assertEquals('manual', $dssd->ketersediaan_source);

        $this->assertDatabaseHas('dssd_opd', [
            'kode_dssd' => 'UPDATED-001',
            'uraian_dssd' => 'Updated uraian',
            'produsen_data' => 'Dinas Baru',
        ]);
    }

    public function test_imported_dssd_update_availability(): void
    {
        $dssd = ImportedDssdData::factory()->create(['ketersediaan_data' => 'tidak']);

        $this->patch(route('imported-dssd-data.availability', $dssd), [
            'ketersediaan_data' => 'ada',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $dssd->refresh();
        $this->assertEquals('ada', $dssd->ketersediaan_data);
        $this->assertEquals('manual', $dssd->ketersediaan_source);
    }

    public function test_imported_dssd_update_availability_rejects_invalid(): void
    {
        $dssd = ImportedDssdData::factory()->create();

        $this->patch(route('imported-dssd-data.availability', $dssd), [
            'ketersediaan_data' => 'maybe',
        ])->assertSessionHasErrors(['ketersediaan_data']);
    }

    public function test_imported_dssd_destroy_deletes_record(): void
    {
        $dssd = ImportedDssdData::factory()->create([
            'kode_dssd' => 'DEL-001',
            'jenis_data' => 'OPD',
            'tahun' => 2026,
            'produsen_data' => 'Dinas Test',
        ]);
        DssdOpd::create([
            'kode_dssd' => 'DEL-001',
            'uraian_dssd' => 'Test',
            'produsen_data' => 'Dinas Test',
            'tahun' => 2026,
        ]);

        $this->delete(route('imported-dssd-data.destroy', $dssd))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('imported_dssd_data', ['id' => $dssd->id]);
        $this->assertDatabaseMissing('dssd_opd', ['kode_dssd' => 'DEL-001']);
    }

    public function test_imported_dssd_store_opd_without_produsen_writes_fallback(): void
    {
        $data = [
            'kode_dssd' => '35.07.01.01.0002',
            'uraian_dssd' => 'Jumlah Penduduk Null Produsen',
            'ketersediaan_data' => 'ada',
            'jenis_data' => 'OPD',
            'produsen_data' => null,
            'tahun' => 2026,
        ];

        $this->post(route('imported-dssd-data.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('dssd_opd', [
            'kode_dssd' => '35.07.01.01.0002',
            'produsen_data' => '[Tanpa Produsen Data]',
        ]);
    }

    public function test_imported_dssd_destroy_all_truncates_table(): void
    {
        ImportedDssdData::factory()->count(5)->create();
        $this->assertEquals(5, ImportedDssdData::count());

        $this->delete(route('imported-dssd-data.destroy-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(0, ImportedDssdData::count());
    }

    public function test_imported_dssd_import_rejects_missing_file(): void
    {
        $this->post(route('imported-dssd-data.import'), [])
            ->assertSessionHasErrors(['file']);
    }

    public function test_dssd_compare_validates_tahun(): void
    {
        $this->post(route('dssd.compare-kamasuta'), [])
            ->assertSessionHasErrors(['tahun']);
    }

    public function test_dssd_compare_rejects_invalid_tahun_range(): void
    {
        $this->post(route('dssd.compare-kamasuta'), ['tahun' => 2019])
            ->assertSessionHasErrors(['tahun']);

        $this->post(route('dssd.compare-kamasuta'), ['tahun' => 2051])
            ->assertSessionHasErrors(['tahun']);
    }

    public function test_dssd_index_filters_by_jenis_data(): void
    {
        ImportedDssdData::factory()->create(['jenis_data' => 'Sektoral']);
        ImportedDssdData::factory()->create(['jenis_data' => 'Spasial']);

        $this->get(route('dssd', ['jenis_data' => 'Sektoral']))->assertOk();
    }

    public function test_dssd_index_filters_by_tahun(): void
    {
        ImportedDssdData::factory()->create(['tahun' => 2025]);
        ImportedDssdData::factory()->create(['tahun' => 2026]);

        $this->get(route('dssd', ['tahun' => 2025]))->assertOk();
    }

    public function test_dssd_index_filters_by_search(): void
    {
        ImportedDssdData::factory()->create(['uraian_dssd' => 'Jumlah Penduduk Miskin']);

        $this->get(route('dssd', ['search' => 'Penduduk']))->assertOk();
    }

    public function test_status_semantics_and_precedence(): void
    {
        $manual1 = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'manual',
            'last_compared_at' => null,
            'matched_kamasuta_code' => null,
        ]);

        $manual2 = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'manual',
            'last_compared_at' => now(),
            'matched_kamasuta_code' => 'XYZ',
        ]);

        $matched1 = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'auto',
            'last_compared_at' => null,
            'matched_kamasuta_code' => 'ABC',
        ]);

        $matched2 = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'auto',
            'last_compared_at' => now(),
            'matched_kamasuta_title' => 'Title',
            'matched_kamasuta_code' => null,
        ]);

        $unmatched = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'auto',
            'last_compared_at' => now(),
            'matched_kamasuta_code' => null,
            'matched_kamasuta_title' => null,
        ]);

        $notSynced = ImportedDssdData::factory()->create([
            'ketersediaan_source' => 'auto',
            'last_compared_at' => null,
            'matched_kamasuta_code' => null,
            'matched_kamasuta_title' => null,
        ]);

        $this->assertEquals(2, ImportedDssdData::filter(['kamasuta_status' => 'manual'])->count());
        $this->assertEquals(2, ImportedDssdData::filter(['kamasuta_status' => 'matched'])->count());
        $this->assertEquals(1, ImportedDssdData::filter(['kamasuta_status' => 'unmatched'])->count());
        $this->assertEquals(1, ImportedDssdData::filter(['kamasuta_status' => 'not_synced'])->count());

        $this->assertTrue(ImportedDssdData::filter(['kamasuta_status' => 'manual'])->get()->contains('id', $manual1->id));
        $this->assertTrue(ImportedDssdData::filter(['kamasuta_status' => 'manual'])->get()->contains('id', $manual2->id));
    }

    public function test_category_semantics(): void
    {
        $sektoral = ImportedDssdData::factory()->create(['kode_dssd' => '35.07.123']);
        $spasial = ImportedDssdData::factory()->create(['kode_dssd' => 'DG.123']);
        $ewalidata = ImportedDssdData::factory()->create(['kode_dssd' => '12.34']);

        $this->assertEquals(1, ImportedDssdData::filter(['kategori_data' => 'Sektoral'])->count());
        $this->assertTrue(ImportedDssdData::filter(['kategori_data' => 'Sektoral'])->get()->contains('id', $sektoral->id));

        $this->assertEquals(1, ImportedDssdData::filter(['kategori_data' => 'Spasial'])->count());
        $this->assertTrue(ImportedDssdData::filter(['kategori_data' => 'Spasial'])->get()->contains('id', $spasial->id));

        $this->assertEquals(1, ImportedDssdData::filter(['kategori_data' => 'e-Walidata'])->count());
        $this->assertTrue(ImportedDssdData::filter(['kategori_data' => 'e-Walidata'])->get()->contains('id', $ewalidata->id));
    }
}
