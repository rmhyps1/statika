<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('imported_dssd_data')
    ->where('produsen_data', 'Badan Perencanaan dan Pembangunan Daerah')
    ->update(['produsen_data' => 'Badan Perencanaan Pembangunan Daerah']);

echo "Berhasil mengupdate " . $count . " baris data BAPPEDA agar sesuai dengan Master List.\n";