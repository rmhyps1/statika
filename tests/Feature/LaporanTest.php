<?php

namespace Tests\Feature;

use App\Models\DataSpasialManual;
use App\Models\ImportedDssdData;
use App\Services\LaporanStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array']);
    }

    public function test_laporan_index_returns_ok(): void
    {
        $this->get(route('laporan.index'))->assertOk();
    }

    public function test_laporan_index_defaults_to_latest_year_and_renders_editable_inputs(): void
    {
        ImportedDssdData::factory()->create([
            'produsen_data' => 'Dinas Pendidikan',
            'tahun' => 2025,
        ]);
        ImportedDssdData::factory()->create([
            'produsen_data' => 'Dinas Kesehatan',
            'tahun' => 2026,
        ]);

        $this->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Tahun 2026')
            ->assertSee('spasial-input');
    }

    public function test_store_spasial_manual_creates_record(): void
    {
        $data = [
            'nama' => 'Dinas Lingkungan Hidup',
            'tahun' => 2026,
            'jumlah_spasial' => 15,
        ];

        $this->post(route('laporan.spasial.manual'), $data)
            ->assertRedirect(route('laporan.index', ['tahun' => 2026]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('data_spasial_manual', [
            'nama_produsen' => 'Dinas Lingkungan Hidup',
            'tahun' => 2026,
            'jumlah_spasial' => 15,
        ]);
    }

    public function test_store_spasial_manual_validates_required_fields(): void
    {
        $this->post(route('laporan.spasial.manual'), [])
            ->assertSessionHasErrors(['nama', 'tahun', 'jumlah_spasial']);
    }

    public function test_get_calculated_stats_includes_manual_only_produsen(): void
    {
        DataSpasialManual::create([
            'nama_produsen' => 'Badan Penanggulangan Bencana Daerah',
            'tahun' => 2026,
            'jumlah_spasial' => 9,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $this->assertEquals(9, $stats['totalSpasial']);

        $bpbdRow = collect($stats['produsenData'])->firstWhere('nama', 'Badan Penanggulangan Bencana Daerah');
        $this->assertNotNull($bpbdRow);
        $this->assertEquals(9, $bpbdRow->jumlah_spasial);
        $this->assertEquals(9, $bpbdRow->total);
        $this->assertEquals(0, $bpbdRow->jumlah_disepakati);
    }

    public function test_get_calculated_stats_output_shape_and_alias(): void
    {
        ImportedDssdData::factory()->create([
            'produsen_data' => 'Dinas Kesehatan',
            'ketersediaan_data' => 'ada',
            'tahun' => 2026,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $this->assertArrayHasKey('produsenData', $stats);
        $this->assertArrayHasKey('totalDisepakati', $stats);
        $this->assertArrayHasKey('totalDilaporkan', $stats);
        $this->assertArrayHasKey('totalKamasuta', $stats);
        $this->assertArrayHasKey('totalSpasial', $stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('persentase', $stats);
        $this->assertEquals($stats['totalKamasuta'], $stats['totalDilaporkan']);
    }

    public function test_get_calculated_stats_percentage_formula_and_rounding(): void
    {
        ImportedDssdData::factory()->create([
            'produsen_data' => 'Dinas Kesehatan',
            'ketersediaan_data' => 'ada',
            'tahun' => 2026,
        ]);
        ImportedDssdData::factory()->count(2)->create([
            'produsen_data' => 'Dinas Kesehatan',
            'ketersediaan_data' => 'tidak',
            'tahun' => 2026,
        ]);

        DataSpasialManual::create([
            'nama_produsen' => 'Dinas Kesehatan',
            'tahun' => 2026,
            'jumlah_spasial' => 1,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $this->assertEquals(3, $stats['totalDisepakati']);
        $this->assertEquals(1, $stats['totalKamasuta']);
        $this->assertEquals(1, $stats['totalSpasial']);
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(66.67, $stats['persentase']);
    }

    public function test_get_calculated_stats_zero_total_disepakati_guard(): void
    {
        DataSpasialManual::create([
            'nama_produsen' => 'Dinas Sosial',
            'tahun' => 2026,
            'jumlah_spasial' => 5,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $this->assertEquals(0, $stats['totalDisepakati']);
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(0, $stats['persentase']);
    }

    public function test_get_calculated_stats_kecamatan_701_grouping(): void
    {
        ImportedDssdData::factory()->create([
            'jenis_data' => 'Kecamatan',
            'kode_dssd' => '7.01.000102',
            'produsen_data' => 'Kecamatan Kepanjen',
            'tahun' => 2026,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $row = collect($stats['produsenData'])->firstWhere('nama', 'Seluruh Kecamatan Se-Kabupaten Malang');
        $this->assertNotNull($row);
        $this->assertEquals('Seluruh Kecamatan Se-Kabupaten Malang', $row->nama);
    }

    public function test_get_calculated_stats_tanpa_produsen_data_grouping(): void
    {
        ImportedDssdData::factory()->create([
            'produsen_data' => null,
            'tahun' => 2026,
        ]);

        $stats = (new LaporanStats)->stats('2026');

        $row = collect($stats['produsenData'])->firstWhere('nama', '[Tanpa Produsen Data]');
        $this->assertNotNull($row);
        $this->assertEquals('[Tanpa Produsen Data]', $row->nama);
    }

    public function test_get_calculated_stats_sorting_by_nama(): void
    {
        ImportedDssdData::factory()->create(['produsen_data' => 'Dinas B', 'tahun' => 2026]);
        ImportedDssdData::factory()->create(['produsen_data' => 'Dinas A', 'tahun' => 2026]);

        $stats = (new LaporanStats)->stats('2026');

        $names = collect($stats['produsenData'])->pluck('nama')->toArray();
        $sortedNames = $names;
        sort($sortedNames);

        $this->assertEquals($sortedNames, $names);
    }

    public function test_laporan_downloads_generate_docx(): void
    {
        $this->withoutExceptionHandling();
        ImportedDssdData::factory()->create([
            'produsen_data' => 'Dinas Kesehatan',
            'ketersediaan_data' => 'ada',
            'tahun' => 2026,
        ]);

        $routes = [
            'laporan.download',
            'laporan.download.template.dilaporkan',
            'laporan.download.template.disepakati',
            'laporan.download.dilaporkan',
            'laporan.download.disepakati',
        ];

        foreach ($routes as $route) {
            $response = $this->post(route($route), [
                'tahun' => 2026,
                'tanggal_ttd' => 'Malang, Februari 2026',
                'jabatan' => 'KEPALA',
                'nama_ttd' => 'Atsalis',
                'pangkat_ttd' => 'Pembina',
                'nip_ttd' => '123',
                'tahun_judul' => '2025',
                'keterangan' => 'Keterangan Test',
            ]);

            if ($response->isRedirect()) {
                dump("Route {$route} failed with error: ".$response->getSession()->get('error'));
            }

            $response->assertOk();
            $response->assertHeader('Content-Disposition');
            $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
            $this->assertStringContainsString('.docx', $response->headers->get('Content-Disposition'));
        }
    }
}
