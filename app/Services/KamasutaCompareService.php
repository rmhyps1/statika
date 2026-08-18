<?php

namespace App\Services;

use App\Models\ImportedDssdData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class KamasutaCompareService
{
    public function compare(int|string $tahun, ?string $jenisData = null): array
    {
        $query = ImportedDssdData::query()->where('tahun', $tahun);

        if ($jenisData) {
            $query->where('jenis_data', $jenisData);
        }

        if (! $query->exists()) {
            return [
                'total' => 0,
                'matched' => 0,
                'unmatched' => 0,
                'skipped_manual' => 0,
                'matched_by_code' => 0,
                'matched_by_title' => 0,
                'missing' => true,
            ];
        }

        $this->buildTempTable($tahun);

        $summary = [
            'total' => 0,
            'matched' => 0,
            'unmatched' => 0,
            'skipped_manual' => 0,
            'matched_by_code' => 0,
            'matched_by_title' => 0,
        ];

        $now = now();

        $query
            ->select(['id', 'kode_dssd', 'uraian_dssd', 'ketersediaan_source', 'last_compared_at', 'tahun'])
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use ($now, &$summary, $tahun) {
                $updates = [];
                $codes = [];
                $titles = [];

                foreach ($rows as $row) {
                    $normCode = substr($this->normalizeCode((string) $row->kode_dssd), 0, 255);
                    if ($normCode !== '') $codes[] = $normCode;
                    
                    $normTitle = substr($this->normalizeText((string) $row->uraian_dssd), 0, 255);
                    if ($normTitle !== '') $titles[] = $normTitle;
                }

                $matchedCodes = !empty($codes) ? DB::table('temp_kamasuta')->whereIn('norm_code', $codes)->get()->keyBy('norm_code')->toArray() : [];
                $matchedTitles = !empty($titles) ? DB::table('temp_kamasuta')->whereIn('norm_title', $titles)->get()->keyBy('norm_title')->toArray() : [];

                foreach ($rows as $row) {
                    $summary['total']++;
                    $match = null;
                    $matchedBy = null;
                    
                    $codeKey = substr($this->normalizeCode((string) $row->kode_dssd), 0, 255);
                    $titleKey = substr($this->normalizeText((string) $row->uraian_dssd), 0, 255);

                    if ($codeKey !== '' && isset($matchedCodes[$codeKey])) {
                        $match = (array) $matchedCodes[$codeKey];
                        $matchedBy = 'kode_dssd';
                        $summary['matched_by_code']++;
                    } elseif ($titleKey !== '' && isset($matchedTitles[$titleKey])) {
                        $match = (array) $matchedTitles[$titleKey];
                        $matchedBy = 'uraian_dssd';
                        $summary['matched_by_title']++;
                    }

                    $summary[$match ? 'matched' : 'unmatched']++;

                    $updates[] = [
                        'id' => $row->id,
                        'kode_dssd' => $row->kode_dssd,
                        'uraian_dssd' => $row->uraian_dssd,
                        'ketersediaan_data' => $match ? 'ada' : 'tidak',
                        'ketersediaan_source' => 'auto',
                        'matched_kamasuta_code' => $match['raw_code'] ?? null,
                        'matched_kamasuta_title' => $match['raw_title'] ?? null,
                        'matched_by' => $matchedBy,
                        'last_compared_at' => $now,
                        'updated_at' => $now,
                        'tahun' => $tahun,
                    ];
                }

                if ($updates) {
                    ImportedDssdData::upsert(
                        $updates,
                        ['id'],
                        ['ketersediaan_data', 'ketersediaan_source', 'matched_kamasuta_code', 'matched_kamasuta_title', 'matched_by', 'last_compared_at', 'updated_at', 'tahun']
                    );
                }
            });

        DB::statement("DROP TABLE IF EXISTS temp_kamasuta");

        return $summary;
    }

    private function buildTempTable(int|string $tahun): void
    {
        DB::statement("DROP TABLE IF EXISTS temp_kamasuta");
        DB::statement("
            CREATE TEMPORARY TABLE temp_kamasuta (
                norm_code VARCHAR(255),
                norm_title VARCHAR(255),
                raw_code TEXT,
                raw_title TEXT
            )
        ");
        DB::statement("CREATE INDEX idx_norm_code ON temp_kamasuta (norm_code)");
        DB::statement("CREATE INDEX idx_norm_title ON temp_kamasuta (norm_title)");

        $client = Http::kamasuta()->timeout(20);
        $page = 1;
        $lastPage = 1;

        do {
            $response = $client->get("/api/indikator/{$tahun}", ['page' => $page, 'per_page' => 1000]);
            
            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error("Kamasuta API failed ({$response->status()}): " . $response->body());
                break;
            }

            $payload = $response->json();
            $rows = $payload['data'] ?? $payload ?? [];
            $inserts = [];

            foreach (is_array($rows) ? $rows : [] as $row) {
                if (!is_array($row)) continue;

                $rawCode = $this->stringValue($row['kodeindikator'] ?? $row['kode'] ?? $row['kode_dssd'] ?? $row['kode_data'] ?? '');
                $rawTitle = $this->stringValue($row['indikator'] ?? $row['judul'] ?? $row['nama_judul_data'] ?? $row['uraian_dssd'] ?? '');
                
                $normCode = substr($this->normalizeCode($rawCode), 0, 255);
                $normTitle = substr($this->normalizeText($rawTitle), 0, 255);

                if ($normCode !== '' || $normTitle !== '') {
                    $inserts[] = [
                        'norm_code' => $normCode,
                        'norm_title' => $normTitle,
                        'raw_code' => $rawCode,
                        'raw_title' => $rawTitle
                    ];
                }
            }

            if (!empty($inserts)) {
                foreach (array_chunk($inserts, 500) as $chunk) {
                    DB::table('temp_kamasuta')->insert($chunk);
                }
            }

            $lastPage = (int) ($payload['last_page'] ?? $page);
            $page++;
        } while ($page <= $lastPage && $page <= 200);
    }

    private function stringValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $value = (array) $value;

            if (isset($value[0])) {
                return implode(', ', array_filter(array_map(fn ($item) => $this->stringValue($item), $value)));
            }

            return (string) ($value['nama'] ?? $value['name'] ?? $value['judul'] ?? json_encode($value));
        }

        return trim((string) $value);
    }

    private function normalizeCode(string $value): string
    {
        return strtoupper(trim(preg_replace('/\s+/', '', $value) ?? ''));
    }

    private function normalizeText(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\s]/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
