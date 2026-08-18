<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class HtmlParserService
{
    public function parseOptions(string $html): array
    {
        if (empty($html)) return [];

        $xpath = $this->xpath($html);
        $nodes = $xpath->query('//option');

        $options = [];
        foreach ($nodes as $node) {
            $value = $node->getAttribute('value');
            if ($value !== '') {
                $options[$value] = trim($node->textContent);
            }
        }

        return $options;
    }

    public function parsePublicRows(string $html, callable $kategoriResolver): array
    {
        if (empty($html)) return [];

        $xpath = $this->xpath($html);
        $rows = $xpath->query('//tr');
        $result = [];

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 6) continue;

            $cellsData = [];
            foreach ($cells as $cell) {
                $cellsData[] = trim(preg_replace('/\s+/', ' ', $cell->textContent));
            }

            $idMatches = [];
            $htmlStr = $row->ownerDocument->saveHTML($row);
            preg_match('/data-nilai-public\?id=(\d+)/', $htmlStr, $idMatches);
            
            $kode = $cellsData[1] ?? '';

            $result[] = [
                'judul_id' => null,
                '_detail_url' => isset($idMatches[1]) ? 'https://kamasuta.malangkab.go.id/data-nilai-public?id=' . $idMatches[1] : null,
                'kode' => $kode,
                'judul' => $cellsData[2] ?? '',
                '_opd_label' => $cellsData[3] ?? '',
                '_jenis_data_label' => $cellsData[4] ?? '',
                'tahun' => preg_replace('/^.*?(\d{4})$/', '$1', $cellsData[5] ?? ''),
                '_kategori_data' => $kategoriResolver($kode),
            ];
        }

        return $result;
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}
