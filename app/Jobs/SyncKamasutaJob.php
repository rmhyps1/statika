<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncKamasutaJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600;

    public $tries = 2;

    protected $tahun;

    protected $jobId;

    public function __construct($tahun, $jobId)
    {
        $this->tahun = $tahun;
        $this->jobId = $jobId;
    }

    public function handle(): void
    {
        $tahun = $this->tahun;
        $jobId = $this->jobId;

        Cache::put("sync_status_{$jobId}", ['status' => 'processing', 'message' => 'Menarik data dari Kamasuta...'], 600);

        try {
            $response = Http::kamasuta()
                ->timeout(60)
                ->get("/api/indikator/{$tahun}", [
                    'per_page' => 500,
                ]);

            if ($response->failed()) {
                throw new Exception('Gagal menarik data dari Kamasuta. HTTP Status: '.$response->status());
            }

            $kamasutaData = $response->json('data');

            if (empty($kamasutaData)) {
                throw new Exception('Data Kamasuta kosong untuk tahun '.$tahun);
            }

            $kodeDssdList = [];
            foreach ($kamasutaData as $row) {
                if (! isset($row['kode_dssd']) || ! isset($row['nilai'])) {
                    continue;
                }
                $kodeDssdList[] = $row['kode_dssd'];
            }

            $matchedCount = 0;

            if (! empty($kodeDssdList)) {
                $matchedCount = DB::table('imported_dssd_data')
                    ->whereIn('kode_dssd', $kodeDssdList)
                    ->where('tahun', $tahun)
                    ->update([
                        'ketersediaan_data' => 'ada',
                        'ketersediaan_source' => 'auto',
                        'last_compared_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            Cache::put("sync_status_{$jobId}", ['status' => 'completed', 'message' => "Sinkronisasi selesai. {$matchedCount} indikator berhasil dicocokkan."], 600);
            Log::info("Kamasuta Sync Job Sukses: {$matchedCount} data terupdate untuk tahun {$tahun}");

        } catch (Exception $e) {
            Log::error("Kamasuta Sync Error [Job: {$jobId}]: ".$e->getMessage()."\n".$e->getTraceAsString());
            Cache::put("sync_status_{$jobId}", ['status' => 'failed', 'message' => $e->getMessage()], 600);
            throw $e;
        }
    }
}
