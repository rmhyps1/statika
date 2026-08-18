<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KamasutaApiService
{
    public function getSumberDataOptions(): array
    {
        try {
            $response = Http::kamasuta()->get('/api/sumber-data');
            $rows = $response->json();
            $options = [];

            foreach (is_array($rows) ? $rows : [] as $row) {
                if (isset($row['sumberdata_id'], $row['nama'])) {
                    $options[$row['sumberdata_id']] = $row['nama'];
                }
            }

            asort($options, SORT_NATURAL | SORT_FLAG_CASE);

            return $options;
        } catch (\Exception $e) {
            Log::warning('Gagal mengambil sumber data Kamasuta: ' . $e->getMessage());
            return [];
        }
    }

    public function fetchJudulList(array $query): array
    {
        return $this->safeApiCall('judul-list', fn () => Http::kamasuta()->get('/api/judul-list', $query));
    }

    public function fetchIndikator(int|string $tahun, array $query = []): array
    {
        return $this->safeApiCall("indikator/{$tahun}", fn () => Http::kamasuta()->get("/api/indikator/{$tahun}", $query));
    }

    public function fetchJudulDetail(int $id): array
    {
        return $this->safeApiCall("judul/{$id}", fn () => Http::kamasuta()->get('/api/judul/' . $id));
    }

    public function fetchPublicJenisData(string $slug, array $query): array
    {
        $fallback = ['tbody' => '', 'opt_opd' => '', 'opt_tahun' => ''];

        try {
            $response = Http::kamasuta()->get('/data-search-public-without-login/' . $slug, $query);
            $payload = $response->json('data') ?? [];

            return [
                'status' => $response->successful(),
                'tbody' => $payload['tbody'] ?? '',
                'opt_opd' => $payload['opt_opd'] ?? '',
                'opt_tahun' => $payload['opt_tahun'] ?? '',
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching Public Jenis Data ($slug): " . $e->getMessage());

            return array_merge($fallback, [
                'status' => false,
                'error' => 'Gagal terhubung ke API Kamasuta: ' . $e->getMessage(),
            ]);
        }
    }

    private function safeApiCall(string $label, callable $request): array
    {
        try {
            $response = $request();

            return [
                'status' => $response->successful(),
                'data' => $response->json(),
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching Kamasuta API ({$label}): " . $e->getMessage());

            return [
                'status' => false,
                'data' => null,
                'error' => 'Gagal terhubung ke API Kamasuta: ' . $e->getMessage(),
            ];
        }
    }
}
