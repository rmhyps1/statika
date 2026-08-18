<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportDssdFileRequest;
use App\Http\Requests\StoreImportedDssdRequest;
use App\Http\Requests\UpdateImportedDssdRequest;
use App\Models\ImportedDssdData;
use App\Services\DssdExportService;
use App\Services\DssdImportService;
use App\Services\DssdMirrorWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportedDssdDataController extends Controller
{
    public function __construct(
        private DssdMirrorWriter $mirrorWriter
    ) {}

    public function store(StoreImportedDssdRequest $request): RedirectResponse
    {
        $item = ImportedDssdData::create($request->validated());
        $this->mirrorWriter->write($item);

        return redirect()->back()->with('success', 'Data DSSD manual berhasil ditambahkan.');
    }

    public function import(ImportDssdFileRequest $request, DssdImportService $service): RedirectResponse
    {
        $uploadedFiles = $request->file('file');

        $totalCount = 0;
        $totalEmptyProdusen = 0;

        foreach ($uploadedFiles as $uploadedFile) {
            $result = $service->processFile(
                $uploadedFile->getRealPath(),
                $uploadedFile->getClientOriginalExtension(),
                $uploadedFile->getClientOriginalName(),
                $request->integer('tahun'),
                $request->input('jenis_data')
            );
            $totalCount += $result['count'];
            $totalEmptyProdusen += $result['empty_produsen'];
        }

        if ($totalCount === 0) {
            return back()->withErrors(['file' => 'Semua file kosong atau tidak valid.']);
        }

        $message = "Berhasil impor {$totalCount} baris data.";
        if ($totalEmptyProdusen > 0) {
            $message .= " Peringatan: {$totalEmptyProdusen} baris tidak memiliki Produsen Data dan masuk ke [Tanpa Produsen Data].";
        }

        return back()->with('success', $message);
    }

    public function update(UpdateImportedDssdRequest $request, ImportedDssdData $importedDssdData): RedirectResponse
    {
        $data = $request->validated();
        $data['ketersediaan_source'] = 'manual';

        $importedDssdData->update($data);
        $this->mirrorWriter->write($importedDssdData);

        return back()->with('success', 'Data import berhasil diperbarui.');
    }

    public function updateAvailability(Request $request, ImportedDssdData $importedDssdData): RedirectResponse
    {
        $request->validate([
            'ketersediaan_data' => ['required', 'in:ada,tidak'],
        ]);

        $importedDssdData->update([
            'ketersediaan_data' => $request->ketersediaan_data,
            'ketersediaan_source' => 'manual',
        ]);

        return back()->with('success', 'Ketersediaan data berhasil diperbarui.');
    }

    public function destroy(ImportedDssdData $importedDssdData): RedirectResponse
    {
        $this->mirrorWriter->delete($importedDssdData);
        $importedDssdData->delete();

        return back()->with('success', 'Data import berhasil dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $criteria = array_filter($request->only(['jenis_data', 'tahun']), fn ($val) => $val !== null && $val !== '');
        $query = ImportedDssdData::filter($criteria);

        foreach ($query->get() as $item) {
            $this->mirrorWriter->delete($item);
        }

        if (! empty($criteria)) {
            $deletedCount = $query->delete();

            return back()->with('success', "Sebanyak {$deletedCount} data import dengan filter yang dipilih berhasil dihapus beserta data terpisahnya.");
        }

        ImportedDssdData::truncate();
        $this->mirrorWriter->truncateAll();

        return back()->with('success', 'Semua data import dan tabel terkaitnya berhasil dikosongkan.');
    }

    public function template()
    {
        $path = $this->templatePath();

        abort_unless(file_exists($path), 404, 'Template DSSD tidak ditemukan.');

        return response()->download($path, 'TEMPLATE_DATA_DSSD.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(Request $request, DssdExportService $exportService)
    {
        $path = $this->templatePath();

        abort_unless(file_exists($path), 404, 'Template DSSD tidak ditemukan.');

        $data = $this->filteredQuery($request)->latest()->get();

        $callback = $exportService->generateExportCallback($path, $data);
        $fileName = 'Export_DSSD_'.date('Y-m-d_H-i-s').'.xlsx';

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return ImportedDssdData::filter($request->all());
    }

    private function templatePath(): string
    {
        return base_path('template/TEMPLATE DATA DSSD.xlsx');
    }
}
