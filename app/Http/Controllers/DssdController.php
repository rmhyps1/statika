<?php

namespace App\Http\Controllers;

use App\Models\ImportedDssdData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DssdController extends Controller
{
    public function index(Request $request): View
    {
        $query = ImportedDssdData::filter($request->all());

        $stats = [
            'total' => (clone $query)->count(),
            'synced' => (clone $query)->status('matched')->count(),
            'not_found' => (clone $query)->status('unmatched')->count(),
            'not_synced' => (clone $query)->status('not_synced')->count(),
        ];

        return view('dssd', [
            'importedData' => $query->latest()->paginate(10, ['*'], 'import_page')->withQueryString(),
            'jenisDataOptions' => ImportedDssdData::distinct()->whereNotNull('jenis_data')->pluck('jenis_data'),
            'produsenDataOptions' => ImportedDssdData::distinct()->whereNotNull('produsen_data')->pluck('produsen_data'),
            'hasImportedData' => ImportedDssdData::exists(),
            'stats' => $stats,
        ]);
    }
}
