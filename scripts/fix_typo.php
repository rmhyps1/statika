<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('imported_dssd_data')
    ->where('produsen_data', 'Bagian Protokol Dan Komunikasi Pimpinan')
    ->update(['produsen_data' => 'Bagian Protokol dan Komunikasi Pimpinan']);

echo "Berhasil membetulkan typo (Dan -> dan) pada " . $count . " baris data.\n";