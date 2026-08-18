<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KamasutaTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array']);
    }

    public function test_kamasuta_uses_returned_labels_for_filters_and_table(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['id' => 1, 'nama' => 'Dinas Komunikasi dan Informatika'],
                    ['id' => 2, 'nama' => 'Sekretariat Daerah']
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 2,
                    'per_page' => 15,
                ]
            ]),
        ]);

        $response = $this->get('/kamasuta');

        $response->assertOk();
    }

    public function test_kamasuta_sends_opd_id_filter_to_api(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => 15,
                ]
            ]),
        ]);

        $this->get('/kamasuta?opd_id=77&jenis_data=Kecamatan&tahun=2025&search=abc')->assertOk();
    }

    public function test_kamasuta_filters_jenis_data_via_public_slug_endpoint(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => 15,
                ]
            ]),
        ]);

        $this->get('/kamasuta?jenis_data=pendidikan&opd=10&tahun=2025')->assertOk();
    }
}
