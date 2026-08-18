<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SyncKamasutaJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncKamasutaJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_throws_exception_if_api_fails()
    {
        putenv('KAMASUTA_API_TOKEN=fake-token');

        Http::fake([
            '*' => Http::response('Server Error', 500)
        ]);

        Log::shouldReceive('error')->once();

        $job = new SyncKamasutaJob('2026', 'job-123');
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gagal menarik data dari Kamasuta');

        $job->handle();
    }
    
    public function test_it_throws_exception_if_data_empty()
    {
        putenv('KAMASUTA_API_TOKEN=fake-token');

        Http::fake([
            '*' => Http::response(['data' => []], 200)
        ]);

        Log::shouldReceive('error')->once();

        $job = new SyncKamasutaJob('2026', 'job-123');
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data Kamasuta kosong untuk tahun 2026');

        $job->handle();
    }
}
