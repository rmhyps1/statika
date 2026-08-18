<?php

namespace App\Http\Controllers;

use App\Services\KamasutaCompareService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DssdKamasutaCompareController extends Controller
{
    public function compare(Request $request, KamasutaCompareService $service): RedirectResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2050',
            'jenis_data' => 'nullable|in:OPD,Kecamatan,Kelurahan',
        ]);

        $tahun = $request->input('tahun');
        $jenisData = $request->input('jenis_data');
        $summary = $service->compare($tahun, $jenisData);

        if ($summary['missing'] ?? false) {
            $label = $jenisData ? " jenis data {$jenisData}" : '';

            return back()->withErrors(['tahun' => "Data DSSD tahun {$tahun}{$label} belum ada. Upload data dulu sebelum compare."]);
        }

        return back()->with(
            'success',
            "Sinkron Kamasuta (Tahun {$tahun}) selesai: {$summary['matched']} cocok, {$summary['unmatched']} tidak ditemukan. Data manual ikut diperbarui menjadi hasil otomatis."
        );
    }
}
