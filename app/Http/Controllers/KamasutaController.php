<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\KamasutaApiService;
use App\Services\HtmlParserService;

class KamasutaController extends Controller
{
    private const JENIS_DATA_SLUGS = [
        'geografis' => 'Geografis',
        'pemerintahan' => 'Pemerintahan',
        'sosial' => 'Sosial',
        'keuangan dan harga' => 'Keuangan dan Harga',
        'penduduk dan tenaga kerja' => 'Penduduk dan Tenaga Kerja',
        'transportasi, komunikasi dan pariwisata' => 'Transportasi, Komunikasi dan Pariwisata',
        'pertanian, peternakan dan pangan' => 'Pertanian, Peternakan dan Pangan',
        'industri dan energi' => 'Industri dan Energi',
        'lingkungan' => 'Lingkungan',
        'kecamatan' => 'Kecamatan',
        'kelurahan' => 'Kelurahan',
        'kesehatan' => 'Kesehatan',
        'pendidikan' => 'Pendidikan',
        'iku' => 'IKU',
        'ikd' => 'IKD',
    ];

    public function __construct(
        private KamasutaApiService $apiService,
        private HtmlParserService $htmlParser
    ) {}

    private function renderView(array $response, string $tab, string $title, ?string $dataWrapper = null, array $filterOptions = [])
    {
        if (!$response['status']) {
            return view('kamasuta', [
                'apiData' => null,
                'apiStatus' => false,
                'errorMessage' => $response['error'] ?? 'Terjadi kesalahan.',
                'activeTab' => $tab,
                'title' => $title,
                'pagination' => null,
                'filterOptions' => ['opd' => []],
            ]);
        }

        $data = $response['data'];
        $pagination = null;
        
        if (isset($data['data']) && is_array($data['data'])) {
            $actualData = $data['data'];
            if (isset($data['current_page']) && isset($data['last_page'])) {
                $pagination = [
                    'current_page' => $data['current_page'],
                    'last_page' => $data['last_page'],
                    'total' => $data['total'] ?? 0,
                    'per_page' => $data['per_page'] ?? 15,
                ];
            }
        } else {
            $actualData = $data;
        }

        $totalData = $pagination['total'] ?? $filterOptions['total_data'] ?? count($actualData ?? []);

        if ($dataWrapper === 'array' && !is_null($actualData) && (isset($actualData['id']) || isset($actualData['judul']) || isset($actualData['nama']))) {
            $apiData = [$actualData];
        } else {
            $apiData = $actualData;
        }

        $opdOptions = $filterOptions['opd'] ?? [];
        $apiData = is_array($apiData) ? array_map(fn ($item) => is_array($item) ? $this->normalizeJudulItem($item, $opdOptions) : $item, $apiData) : $apiData;

        return view('kamasuta', [
            'apiData' => $apiData,
            'apiStatus' => true,
            'activeTab' => $tab,
            'title' => $title,
            'pagination' => $pagination,
            'filterOptions' => $this->filterOptions($apiData, $filterOptions),
            'totalDataCard' => $totalData
        ]);
    }

    private function normalizeJudulItem(array $item, array $opdOptions = []): array
    {
        $opdId = $item['opd_id'] ?? null;
        $item['_opd_label'] = $opdOptions[$opdId] ?? $this->label($item['sumber_data'] ?? $item['opd'] ?? $item['produsen_data'] ?? null);
        $item['_jenis_data_label'] = $this->jenisDataLabel($item);

        $kode = $item['kode'] ?? $item['kode_dssd'] ?? $item['kode_data'] ?? '-';
        $item['_kategori_data'] = $this->getKategoriByKode($kode);

        return $item;
    }

    private function getKategoriByKode(string|array|object $kode): string
    {
        if (is_array($kode) || is_object($kode)) {
            $kodeArr = (array)$kode;
            $kode = isset($kodeArr[0]) ? implode(', ', array_map(fn($i) => ((array)$i)['nama'] ?? ((array)$i)['name'] ?? '', $kodeArr)) : ($kodeArr['nama'] ?? $kodeArr['name'] ?? json_encode($kode));
        }

        if (str_starts_with((string)$kode, '35')) {
            return 'Sektoral';
        } elseif (str_starts_with((string)$kode, 'DG.')) {
            return 'Spasial';
        }
        return 'e-Walidata';
    }

    private function filterOptions(mixed $apiData, array $filterOptions = []): array
    {
        $items = is_array($apiData) ? $apiData : [];

        return [
            'opd' => $filterOptions['opd'] ?? $this->uniqueOpdOptions($items),
            'opd_param' => $filterOptions['opd_param'] ?? 'opd_id',
            'jenis_data' => self::JENIS_DATA_SLUGS,
            'tahun' => $filterOptions['tahun'] ?? [],
        ];
    }

    private function uniqueOpdOptions(array $items): array
    {
        $options = [];
        foreach ($items as $item) {
            $id = $item['opd_id'] ?? $item['sumber_data']['sumberdata_id'] ?? null;
            $label = $item['_opd_label'] ?? '';
            if ($id && $label) {
                $options[$id] = $label;
            }
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }


    private function jenisDataLabel(array $item): string
    {
        $jenisMap = [
            14 => 'Pemerintahan',
            22 => 'Kecamatan',
            23 => 'Kelurahan',
        ];
        $jenisId = $item['jenis_data_id'] ?? null;

        return $this->label($item['jenis_data'] ?? ($jenisId ? ($jenisMap[$jenisId] ?? null) : null) ?? $item['kelompok_kolom'] ?? null);
    }

    private function label(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $value = (array) $value;

            if (isset($value[0])) {
                return implode(', ', array_filter(array_map(fn ($item) => $this->label($item), $value)));
            }

            return $value['nama'] ?? $value['name'] ?? $value['jenis_data'] ?? json_encode($value);
        }

        return (string) ($value ?: '');
    }

    private function normalizeSearchText(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9\s]/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function searchKeywords(?string $search): array
    {
        $search = $this->normalizeSearchText((string) $search);

        return array_values(array_filter(explode(' ', $search), fn ($word) => $word !== ''));
    }

    private function filterBySearch(array $items, ?string $search): array
    {
        $keywords = $this->searchKeywords($search);

        if (!$keywords) {
            return $items;
        }

        $filtered = array_filter($items, function ($item) use ($keywords) {
            if (!is_array($item)) {
                return false;
            }

            $haystack = $this->normalizeSearchText(implode(' ', array_filter([
                $item['judul'] ?? null,
                $item['nama_judul_data'] ?? null,
                $item['uraian_dssd'] ?? null,
            ], fn ($value) => is_scalar($value))));

            foreach ($keywords as $keyword) {
                if (!preg_match('/\b' . preg_quote($keyword, '/') . '/i', $haystack)) {
                    return false;
                }
            }

            return true;
        });

        return array_values($filtered);
    }

    private function filterByTahun(array $items, ?string $tahun): array
    {
        if (!$tahun) {
            return $items;
        }

        $filtered = array_filter($items, function ($item) use ($tahun) {
            if (!is_array($item)) return false;
            
            $itemTahun = $item['tahun'] ?? null;
            
            if (is_array($itemTahun)) {
                return in_array($tahun, $itemTahun);
            }
            
            return (string)$itemTahun === (string)$tahun;
        });

        return array_values($filtered);
    }

    private function manualPaginate(array $items, Request $request, int $perPage = 10): array
    {
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) $request->query('page', 1);
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($items, $offset, $perPage),
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ];
    }

    private function sortItems(array $items, Request $request): array
    {
        $sortBy = $request->query('sort_by');
        $sortDir = strtolower($request->query('sort_dir', 'asc'));

        $validColumns = [
            'kode' => ['kode', 'kode_dssd', 'kode_data'],
            'judul' => ['judul', 'nama_judul_data', 'uraian_dssd'],
            'opd' => ['_opd_label'],
            'tahun' => ['tahun'],
            'jenis_data' => ['_jenis_data_label']
        ];

        if (!$sortBy || !isset($validColumns[$sortBy])) {
            return $items;
        }

        $keys = $validColumns[$sortBy];

        usort($items, function ($a, $b) use ($keys, $sortDir, $sortBy) {
            $valA = '';
            $valB = '';
            
            foreach ($keys as $key) {
                if (isset($a[$key])) { $valA = $a[$key]; break; }
            }
            foreach ($keys as $key) {
                if (isset($b[$key])) { $valB = $b[$key]; break; }
            }

            $valA = is_scalar($valA) ? strtolower(trim((string)$valA)) : '';
            $valB = is_scalar($valB) ? strtolower(trim((string)$valB)) : '';

            if ($sortBy === 'tahun') {
                $valA = (int)$valA;
                $valB = (int)$valB;
            }

            if ($valA === $valB) return 0;
            
            $result = ($valA < $valB) ? -1 : 1;
            return $sortDir === 'desc' ? -$result : $result;
        });

        return $items;
    }

    private function cachedJudulListItems(Request $request): array
    {
        $baseQuery = array_filter($request->only(['opd_id']), fn ($value) => $value !== null && $value !== '');
        $cacheKey = 'kamasuta_judul_list_' . md5(json_encode($baseQuery));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($baseQuery) {
            $items = [];
            $page = 1;
            $lastPage = 1;
            $perPage = 1000;
            $maxPages = 30;

            do {
                $response = $this->apiService->fetchJudulList(array_merge($baseQuery, [
                    'page' => $page,
                    'per_page' => $perPage,
                ]));

                if (!$response['status']) {
                    break;
                }

                $payload = $response['data'] ?? [];
                $rows = $payload['data'] ?? $payload ?? [];

                if (!is_array($rows)) {
                    break;
                }

                $items = array_merge($items, array_values($rows));
                $lastPage = (int) ($payload['last_page'] ?? $page);
                $page++;
            } while ($page <= $lastPage && $page <= $maxPages);

            return $items;
        });
    }

    private function publicJenisData(string $slug, Request $request)
    {
        $query = array_filter($request->only(['opd', 'tahun']), fn ($value) => $value !== null && $value !== '');
        
        $response = $this->apiService->fetchPublicJenisData($slug, $query);

        if (!$response['status']) {
             return view('kamasuta', [
                'apiData' => null,
                'apiStatus' => false,
                'errorMessage' => $response['error'],
                'activeTab' => 'judul-list',
                'title' => 'Daftar Judul Data Kamasuta',
                'pagination' => null,
                'filterOptions' => ['opd' => []],
            ]);
        }

        $kategoriResolver = fn($kode) => $this->getKategoriByKode($kode);
        $allData = $this->htmlParser->parsePublicRows($response['tbody'], $kategoriResolver);
        
        $allData = $this->filterByTahun($allData, $request->query('tahun'));
        $allData = $this->filterBySearch($allData, $request->query('search'));
        $allData = $this->sortItems($allData, $request);
        $pagination = $this->manualPaginate($allData, $request);

        return view('kamasuta', [
            'apiData' => $pagination['data'],
            'apiStatus' => true,
            'activeTab' => 'judul-list',
            'title' => 'Daftar Judul Data Kamasuta',
            'pagination' => $pagination,
            'filterOptions' => $this->filterOptions([], [
                'opd' => $this->htmlParser->parseOptions($response['opt_opd']),
                'opd_param' => 'opd',
                'tahun' => $this->htmlParser->parseOptions($response['opt_tahun']),
            ]),
            'totalDataCard' => $pagination['total']
        ]);
    }

    public function judulList(Request $request)
    {
        $jenisSlug = $request->query('jenis_data');
        if ($jenisSlug && isset(self::JENIS_DATA_SLUGS[$jenisSlug])) {
            return $this->publicJenisData($jenisSlug, $request);
        }

        if ($request->filled('search') || $request->filled('sort_by') || $request->filled('tahun')) {
            $items = $this->cachedJudulListItems($request);
            $items = $this->filterByTahun($items, $request->query('tahun'));
            $items = $this->filterBySearch($items, $request->query('search'));
            $items = $this->sortItems($items, $request);
            $pagination = $this->manualPaginate($items, $request);

            return $this->renderView(
                [
                    'status' => true,
                    'data' => $pagination,
                    'error' => null,
                ],
                'judul-list',
                'Daftar Judul Data Kamasuta',
                null,
                [
                    'opd' => $this->apiService->getSumberDataOptions(),
                    'total_data' => $pagination['total'],
                ]
            );
        }

        $query = array_filter($request->only(['page', 'opd_id', 'tahun']), fn ($value) => $value !== null && $value !== '');
        $query['page'] = $query['page'] ?? 1;

        $response = $this->apiService->fetchJudulList($query);
        $totalData = $response['data']['total'] ?? 0;

        return $this->renderView(
            $response,
            'judul-list',
            'Daftar Judul Data Kamasuta',
            null,
            [
                'opd' => $this->apiService->getSumberDataOptions(),
                'total_data' => $totalData
            ]
        );
    }

    public function judulDetail(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return back()->withErrors(['id' => 'ID detail Kamasuta tidak ditemukan.']);
        }

        $response = $this->apiService->fetchJudulDetail((int) $id);

        return $this->renderView($response, 'judul-detail', 'Detail Judul (ID: '.$id.')', 'array');
    }
}
