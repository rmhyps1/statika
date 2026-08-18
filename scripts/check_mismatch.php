<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$dssdCount = DB::table('imported_dssd_data')->count();
$dssdSynced = DB::table('imported_dssd_data')->where('ketersediaan_data', 'ada')->count();

$laporanStats = DB::table('imported_dssd_data as d')
    ->join('produsen_data as p', 'd.produsen_data', '=', 'p.nama')
    ->selectRaw('COUNT(d.id) as total, SUM(CASE WHEN d.ketersediaan_data = "ada" THEN 1 ELSE 0 END) as synced')
    ->first();

echo "Total Data DSSD Mentah  : " . $dssdCount . " (Synced: " . $dssdSynced . ")\n";
echo "Total Laporan Exact Map : " . $laporanStats->total . " (Synced: " . $laporanStats->synced . ")\n\n";

$unmapped = DB::select("
    SELECT d.produsen_data, COUNT(d.id) as jumlah, SUM(CASE WHEN d.ketersediaan_data = 'ada' THEN 1 ELSE 0 END) as synced 
    FROM imported_dssd_data d
    LEFT JOIN produsen_data p ON d.produsen_data = p.nama
    WHERE p.id IS NULL
    GROUP BY d.produsen_data
    ORDER BY jumlah DESC
");

echo "Instansi di DSSD yang GAGAL MASUK LAPORAN (Typo/Tidak ada di Master 99):\n";
foreach ($unmapped as $row) {
    echo "- " . ($row->produsen_data ?: '[KOSONG]') . " : " . $row->jumlah . " data (Synced: " . $row->synced . ")\n";
}