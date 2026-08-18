<?php

namespace App\Services;

use App\Models\DssdOpd;
use App\Models\ImportedDssdData;
use App\Models\Kecamatan;
use App\Models\Kelurahan;

class DssdMirrorWriter
{
    public function write(ImportedDssdData $item): void
    {
        $attributes = [
            'uraian_dssd' => $item->uraian_dssd,
            'produsen_data' => $item->produsen_data,
            'jenis_data' => $item->jenis_data,
            'jenis_produsen' => $item->jenis_produsen,
            'tahun' => $item->tahun,
            'satuan' => $item->satuan,
            'definisi_operasional' => $item->definisi_operasional,
            'tag_urusan' => $item->tag_urusan,
            'info_sub_kegiatan' => $item->info_sub_kegiatan,
            'keterangan' => $item->keterangan,
        ];

        if ($item->jenis_data === 'OPD') {
            $attributes['produsen_data'] = $item->produsen_data ?: '[Tanpa Produsen Data]';
            DssdOpd::updateOrCreate(
                ['kode_dssd' => $item->kode_dssd],
                $attributes
            );
        } elseif ($item->jenis_data === 'Kecamatan') {
            $attributes['nama_kecamatan'] = $item->produsen_data ?: '[Tanpa Produsen Data]';
            Kecamatan::updateOrCreate(
                ['kode_kecamatan' => $item->kode_dssd],
                $attributes
            );
        } elseif ($item->jenis_data === 'Kelurahan') {
            $kecamatan = Kecamatan::firstOrCreate(
                ['kode_kecamatan' => substr($item->kode_dssd, 0, 7) ?: '00.0000'],
                ['nama_kecamatan' => 'Unknown']
            );

            $attributes['nama_kelurahan'] = $item->produsen_data ?: '[Tanpa Produsen Data]';
            $attributes['kecamatan_id'] = $kecamatan->id;

            Kelurahan::updateOrCreate(
                ['kode_kelurahan' => $item->kode_dssd],
                $attributes
            );
        }
    }

    public function delete(ImportedDssdData $item): void
    {
        if ($item->jenis_data === 'OPD') {
            DssdOpd::where('kode_dssd', $item->kode_dssd)->where('tahun', $item->tahun)->delete();
        } elseif ($item->jenis_data === 'Kecamatan') {
            Kecamatan::where('kode_kecamatan', $item->kode_dssd)->where('tahun', $item->tahun)->delete();
        } elseif ($item->jenis_data === 'Kelurahan') {
            Kelurahan::where('kode_kelurahan', $item->kode_dssd)->where('tahun', $item->tahun)->delete();
        }
    }

    public function truncateAll(): void
    {
        DssdOpd::truncate();
        Kelurahan::query()->delete();
        Kecamatan::query()->delete();
    }
}
