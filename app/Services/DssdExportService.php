<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Collection;
use Closure;

class DssdExportService
{
    public function generateExportCallback(string $templatePath, Collection $data): Closure
    {
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;
        $number = 1;

        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $number++);
            $sheet->setCellValue('B' . $row, $item->kode_dssd);
            $sheet->setCellValue('C' . $row, $item->uraian_dssd);
            $sheet->setCellValue('D' . $row, $item->satuan);
            $sheet->setCellValue('E' . $row, $item->definisi_operasional);
            $sheet->setCellValue('F' . $row, $item->tag_urusan);
            $sheet->setCellValue('G' . $row, $item->produsen_data);
            $sheet->setCellValue('H' . $row, $item->info_sub_kegiatan);
            $sheet->setCellValue('I' . $row, $item->ketersediaan_data === 'ada' ? 'Ada' : 'Tidak');
            $sheet->setCellValue('J' . $row, $item->keterangan);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return function () use ($writer) {
            $writer->save('php://output');
        };
    }
}
