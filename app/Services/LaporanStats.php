<?php

namespace App\Services;

use App\Models\DataSpasialManual;
use Illuminate\Support\Facades\DB;

class LaporanStats
{
    public function stats(?string $tahun = null): array
    {
        $namaLaporan = $this->namaLaporanExpression();
        $query = DB::table('imported_dssd_data')
            ->select([
                DB::raw('MIN(id) as id'),
                DB::raw("{$namaLaporan} as nama"),
                DB::raw('COUNT(id) as jumlah_disepakati'),
                DB::raw('SUM(CASE WHEN ketersediaan_data = "ada" THEN 1 ELSE 0 END) as jumlah_dilaporkan'),
            ])
            ->groupBy(DB::raw($namaLaporan))
            ->orderBy('nama');

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $spasialQuery = DataSpasialManual::query();
        if ($tahun) {
            $spasialQuery->where('tahun', $tahun);
        }
        $spasialMap = $spasialQuery->get()->keyBy('nama_produsen');

        $produsenData = $query->get()->map(function ($stat) use ($spasialMap) {
            $spasial = $spasialMap->get($stat->nama);
            $jumlahSpasial = $spasial ? (int) $spasial->jumlah_spasial : 0;
            $jumlahKamasuta = (int) $stat->jumlah_dilaporkan;
            $total = $jumlahKamasuta + $jumlahSpasial;

            return (object) [
                'id' => $stat->id,
                'nama' => $stat->nama,
                'jumlah_disepakati' => (int) $stat->jumlah_disepakati,
                'jumlah_dilaporkan' => $jumlahKamasuta,
                'jumlah_kamasuta' => $jumlahKamasuta,
                'jumlah_spasial' => $jumlahSpasial,
                'total' => $total,
                '_is_disepakati_auto' => true,
                '_is_dilaporkan_auto' => true,
                '_is_kamasuta_auto' => true,
                '_is_spasial_auto' => false,
            ];
        });

        $existingNames = $produsenData->keyBy('nama');
        foreach ($spasialMap as $namaProdusen => $spasialRecord) {
            if (! $existingNames->has($namaProdusen)) {
                $jumlahSpasial = (int) $spasialRecord->jumlah_spasial;
                $produsenData->push((object) [
                    'id' => null,
                    'nama' => $namaProdusen,
                    'jumlah_disepakati' => 0,
                    'jumlah_dilaporkan' => 0,
                    'jumlah_kamasuta' => 0,
                    'jumlah_spasial' => $jumlahSpasial,
                    'total' => $jumlahSpasial,
                    '_is_disepakati_auto' => false,
                    '_is_dilaporkan_auto' => false,
                    '_is_kamasuta_auto' => false,
                    '_is_spasial_auto' => false,
                ]);
            }
        }
        $produsenData = $produsenData->sortBy('nama')->values();

        $totalDisepakati = $produsenData->sum('jumlah_disepakati');
        $totalKamasuta = $produsenData->sum('jumlah_kamasuta');
        $totalDilaporkan = $totalKamasuta;
        $totalSpasial = $produsenData->sum('jumlah_spasial');
        $total = $totalKamasuta + $totalSpasial;
        $persentase = $totalDisepakati > 0 ? round(($total / $totalDisepakati) * 100, 2) : 0;

        return compact('produsenData', 'totalDisepakati', 'totalDilaporkan', 'totalKamasuta', 'totalSpasial', 'total', 'persentase');
    }

    private function namaLaporanExpression(): string
    {
        return "CASE
            WHEN jenis_data = 'Kecamatan' AND kode_dssd LIKE '7.01%' THEN 'Seluruh Kecamatan Se-Kabupaten Malang'
            WHEN produsen_data IS NULL OR TRIM(produsen_data) = '' THEN '[Tanpa Produsen Data]'
            ELSE produsen_data
        END";
    }
}
