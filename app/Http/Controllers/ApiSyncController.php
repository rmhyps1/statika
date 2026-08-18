<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Jobs\SyncKamasutaJob;

class ApiSyncController extends Controller
{
    public function syncKamasuta(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $jobId = (string) Str::uuid();
        
        Cache::put("sync_status_{$jobId}", ['status' => 'pending', 'message' => 'Menyiapkan proses sinkronisasi...'], 600);
        
        SyncKamasutaJob::dispatch($tahun, $jobId);

        return response()->json([
            'status' => 'success',
            'job_id' => $jobId
        ]);
    }

    public function checkSyncStatus($jobId)
    {
        $status = Cache::get("sync_status_{$jobId}", ['status' => 'not_found', 'message' => 'Status tidak ditemukan.']);
        return response()->json($status);
    }
}
