<?php

namespace App\Services;

use App\Models\ImportedDssdData;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class DssdImportService
{
    public function __construct(
        private DssdMirrorWriter $mirrorWriter
    ) {}

    public function processFile(string $path, string $extension, ?string $filename = null, int|string|null $tahun = null, ?string $jenisData = 'OPD'): array
    {
        $rows = $this->rows($path, $extension);
        $count = 0;
        $emptyProdusenCount = 0;
        $source = in_array($jenisData, ['OPD', 'Kecamatan', 'Kelurahan'], true) ? $jenisData : 'OPD';

        if (! $rows) {
            return ['count' => 0, 'empty_produsen' => 0];
        }

        DB::transaction(function () use ($rows, &$count, &$emptyProdusenCount, $tahun, $source) {
            $insertData = [];
            $now = now();

            foreach ($rows as $data) {
                $kodeDssd = $this->value($data, ['kode dssd', 'kode_dssd', 'kode data', 'kode_data']);
                $uraianDssd = $this->value($data, ['uraian dssd', 'uraian_dssd']);

                if (! $kodeDssd || ! $uraianDssd) {
                    continue;
                }

                $finalTahun = $this->year((string) $tahun) ?: $this->year($this->value($data, ['tahun']));
                $produsenData = $this->normalizeProdusenData($this->value($data, ['produsen data', 'produsen_data']));
                $rowJenisData = $this->detectJenisData($data, $source);

                if (! $produsenData) {
                    $emptyProdusenCount++;
                }

                $attributes = $this->mapRowAttributes($data, $kodeDssd, $uraianDssd, $produsenData, $rowJenisData, $finalTahun);

                $existCheck = ImportedDssdData::where('kode_dssd', $kodeDssd)
                    ->where('jenis_data', $rowJenisData)
                    ->where('tahun', $finalTahun)
                    ->first();

                if ($existCheck) {
                    $existCheck->update(array_merge($attributes, ['updated_at' => $now]));
                    $count++;
                    $this->mirrorWriter->write($existCheck);

                    continue;
                }

                $insertData[] = array_merge($attributes, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $this->mirrorWriter->write(new ImportedDssdData($attributes));

                if (count($insertData) >= 500) {
                    ImportedDssdData::insert($insertData);
                    $count += count($insertData);
                    $insertData = [];
                }
            }

            if (! empty($insertData)) {
                ImportedDssdData::insert($insertData);
                $count += count($insertData);
            }
        });

        return ['count' => $count, 'empty_produsen' => $emptyProdusenCount];
    }

    private function mapRowAttributes(array $data, string $kodeDssd, string $uraianDssd, ?string $produsenData, string $rowJenisData, ?string $finalTahun): array
    {
        $ketersediaanData = strtolower($this->value($data, ['ketersediaan data', 'ketersediaan_data'], 'tidak')) === 'ada' ? 'ada' : 'tidak';

        return [
            'kode_dssd' => $kodeDssd,
            'uraian_dssd' => $uraianDssd,
            'produsen_data' => $produsenData,
            'ketersediaan_data' => $ketersediaanData,
            'ketersediaan_source' => 'upload',
            'jenis_data' => $rowJenisData,
            'jenis_produsen' => $this->normalizeProdusenData($this->value($data, ['jenis produsen', 'jenis_produsen'], $produsenData)),
            'satuan' => $this->value($data, ['satuan']),
            'definisi_operasional' => $this->value($data, ['definisi operasional', 'definisi_operasional']),
            'tag_urusan' => $this->value($data, ['tag urusan', 'tag_urusan']),
            'info_sub_kegiatan' => $this->value($data, ['info sub kegiatan', 'info_sub_kegiatan']),
            'keterangan' => $this->value($data, ['keterangan']),
            'tahun' => $finalTahun,
            'raw_data' => json_encode($data),
        ];
    }

    private function rows(string $path, string $extension): array
    {
        $isCsv = in_array(strtolower($extension), ['csv', 'txt'], true);
        $reader = $isCsv ? new Csv : IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);

        $sheets = $isCsv ? [$spreadsheet->getActiveSheet()] : $spreadsheet->getAllSheets();
        $rows = [];

        foreach ($sheets as $sheet) {
            $sheetRows = $sheet->toArray(null, true, true, true);
            if (empty($sheetRows)) {
                continue;
            }

            $rawHeader = array_shift($sheetRows) ?: [];
            $header = array_map(fn ($item) => strtolower(trim((string) $item)), array_values($rawHeader));

            $sheetTitle = strtolower(trim($sheet->getTitle()));
            if ($sheetTitle === 'spasial' || ! in_array('uraian dssd', $header, true)) {
                continue;
            }

            foreach ($sheetRows as $sheetRow) {
                $row = array_combine($header, array_pad(array_values($sheetRow), count($header), null));

                if (array_filter($row, fn ($value) => $value !== null && $value !== '')) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    private function value(array $data, array $keys, ?string $default = null): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return $default;
    }

    private function normalizeProdusenData(?string $value): ?string
    {
        return $value === 'Badan Perencanaan dan Pembangunan Daerah'
            ? 'Badan Perencanaan Pembangunan Daerah'
            : $value;
    }

    private function year(?string $value): ?string
    {
        return preg_match('/^(19|20|21)\d{2}$/', (string) $value) ? $value : null;
    }

    private function detectJenisData(array $data, string $default = 'OPD'): string
    {
        $explicit = $this->value($data, ['jenis data', 'jenis_data']);
        if ($explicit && in_array(trim($explicit), ['OPD', 'Kecamatan', 'Kelurahan'], true)) {
            return trim($explicit);
        }

        $produsen = strtolower(trim((string) $this->value($data, ['produsen data', 'produsen_data'], '')));
        if (str_starts_with($produsen, 'kecamatan')) {
            return 'Kecamatan';
        }
        if (str_starts_with($produsen, 'kelurahan')) {
            return 'Kelurahan';
        }

        return $default;
    }
}
