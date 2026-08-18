<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class ReportGeneratorService
{
    public function generatePersentaseDocx(array $data, array $options): string
    {
        $templateProcessor = $this->loadTemplate('Persentase Daftar Data yang Dilaporkan_lembar ke-1.docx');

        $templateProcessor->setValue('dlpr', (string) $data['totalDilaporkan']);
        $templateProcessor->setValue('dspk', (string) $data['totalDisepakati']);
        $templateProcessor->setValue('persen', (string) $data['persentase']);

        $this->applySignatureOptions($templateProcessor, $options);

        return $this->saveTemplate($templateProcessor, "Laporan_DSSD", $options);
    }

    public function generateBaseDocx(string $type, bool $isEmptyTemplate, Collection $produsenData, array $options): string
    {
        $templateName = $type === 'dilaporkan'
            ? 'Jumlah Daftar Data yang Dilaporkan_lembar ke-1.docx'
            : 'Jumlah Daftar Data yang Disepakati_lembar ke-2.docx';

        $templateProcessor = $this->loadTemplate($templateName);

        $this->applySignatureOptions($templateProcessor, $options);
        $templateProcessor->setValue('keterangan', $options['keterangan'] ?? '');

        $totalJumlah = 0;
        $produsenArray = $produsenData->values()->toArray();

        for ($rowNum = 1; $rowNum <= 99; $rowNum++) {
            $itemIndex = $rowNum - 1;
            $hasItem = isset($produsenArray[$itemIndex]);

            if ($hasItem && ! $isEmptyTemplate) {
                $item = $produsenArray[$itemIndex];
                $val = $type === 'dilaporkan' ? (int) $item->jumlah_dilaporkan : (int) $item->jumlah_disepakati;
                $totalJumlah += $val;
                $templateProcessor->setValue('jml_'.$rowNum, (string) $val);
            } else {
                $templateProcessor->setValue('jml_'.$rowNum, '');
            }
        }

        $templateProcessor->setValue('total', $isEmptyTemplate ? '' : (string) $totalJumlah);

        $typeLabel = ucfirst($type);
        $prefix = $isEmptyTemplate ? 'Template' : 'Rekapitulasi';

        return $this->saveTemplate($templateProcessor, "{$prefix}_{$typeLabel}_DSSD", $options);
    }

    private function loadTemplate(string $templateName): TemplateProcessor
    {
        $templatePath = base_path('template/' . $templateName);

        if (! file_exists($templatePath)) {
            throw new Exception("Template file {$templateName} tidak ditemukan di folder template/.");
        }

        return new TemplateProcessor($templatePath);
    }

    private function applySignatureOptions(TemplateProcessor $processor, array $options): void
    {
        $processor->setValue('tanggal_ttd', $options['tanggal_ttd']);
        $processor->setValue('jabatan', $options['jabatan']);
        $processor->setValue('nama_ttd', $options['nama_ttd']);
        $processor->setValue('pangkat_ttd', $options['pangkat_ttd']);
        $processor->setValue('nip_ttd', $options['nip_ttd']);
        $processor->setValue('tahun_judul', $options['tahun_judul']);
    }

    private function saveTemplate(TemplateProcessor $processor, string $filePrefix, array $options): string
    {
        $tahunLabel = ! empty($options['tahun']) ? "_{$options['tahun']}" : '_Semua_Tahun';
        $fileName = "{$filePrefix}{$tahunLabel}.docx";
        $outputPath = storage_path("app/public/{$fileName}");

        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        try {
            $processor->saveAs($outputPath);
        } catch (Exception $e) {
            Log::error('ReportGeneratorService Error: ' . $e->getMessage());
            throw new Exception('Gagal membuat laporan DOCX: ' . $e->getMessage());
        }

        return $outputPath;
    }
}
