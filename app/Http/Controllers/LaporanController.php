<?php

namespace App\Http\Controllers;

use App\Models\DataSpasialManual;
use App\Models\ImportedDssdData;
use App\Services\LaporanStats;
use App\Services\ReportGeneratorService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(
        private LaporanStats $laporanStats
    ) {}

    public function index(Request $request)
    {
        $tahunFilter = $request->input('tahun') ?: null;

        $dssdYears = ImportedDssdData::select('tahun')->whereNotNull('tahun')->where('tahun', '!=', '')->distinct()->pluck('tahun');
        $spasialYears = DataSpasialManual::select('tahun')->whereNotNull('tahun')->where('tahun', '!=', '')->distinct()->pluck('tahun');
        $availableYears = $dssdYears->merge($spasialYears)->unique()->sort()->values();

        $data = $this->laporanStats->stats($tahunFilter);

        $instansiOptions = $data['produsenData']->pluck('nama')->toArray();

        $tanpaProdusen = $data['produsenData']->firstWhere('nama', '[Tanpa Produsen Data]');
        $tanpaProdusenCount = $tanpaProdusen ? $tanpaProdusen->jumlah_disepakati : 0;

        return view('laporan', array_merge($data, [
            'tahunFilter' => $tahunFilter,
            'availableYears' => $availableYears,
            'instansiOptions' => $instansiOptions,
            'tanpaProdusenCount' => $tanpaProdusenCount,
        ]));
    }

    public function storeSpasialManual(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:1900', 'max:2100'],
            'jumlah_spasial' => ['required', 'integer', 'min:0'],
        ]);

        $this->upsertSpasial($validated);

        return redirect()->route('laporan.index', ['tahun' => $validated['tahun']])
            ->with('success', "Data spasial manual untuk '{$validated['nama']}' berhasil disimpan.");
    }

    public function updateSpasial(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'jumlah_spasial' => ['required', 'integer', 'min:0'],
        ]);

        $this->upsertSpasial($validated);

        $data = $this->laporanStats->stats($validated['tahun']);

        return response()->json([
            'success' => true,
            'total' => $data['total'],
            'totalSpasial' => $data['totalSpasial'],
            'persentase' => $data['persentase'],
        ]);
    }

    public function downloadDocx(Request $request, ReportGeneratorService $reportService)
    {
        $data = $this->laporanStats->stats($request->input('tahun'));
        $options = $this->extractReportOptions($request);

        return $this->tryDownload(fn () => $reportService->generatePersentaseDocx($data, $options));
    }

    public function downloadTemplateDilaporkan(Request $request, ReportGeneratorService $reportService)
    {
        return $this->downloadBase('dilaporkan', true, $request, $reportService);
    }

    public function downloadTemplateDisepakati(Request $request, ReportGeneratorService $reportService)
    {
        return $this->downloadBase('disepakati', true, $request, $reportService);
    }

    public function downloadDilaporkanDocx(Request $request, ReportGeneratorService $reportService)
    {
        return $this->downloadBase('dilaporkan', false, $request, $reportService);
    }

    public function downloadDisepakatiDocx(Request $request, ReportGeneratorService $reportService)
    {
        return $this->downloadBase('disepakati', false, $request, $reportService);
    }

    private function downloadBase(string $type, bool $isEmptyTemplate, Request $request, ReportGeneratorService $reportService)
    {
        $data = $this->laporanStats->stats($request->input('tahun'));
        $options = $this->extractReportOptions($request);

        return $this->tryDownload(fn () => $reportService->generateBaseDocx($type, $isEmptyTemplate, $data['produsenData'], $options));
    }

    private function tryDownload(callable $generator)
    {
        try {
            return response()->download($generator())->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->route('laporan.index')->with('error', $e->getMessage());
        }
    }

    private function upsertSpasial(array $validated): void
    {
        DataSpasialManual::updateOrCreate(
            [
                'nama_produsen' => $validated['nama'],
                'tahun' => $validated['tahun'],
            ],
            [
                'jumlah_spasial' => $validated['jumlah_spasial'],
            ]
        );
    }

    private function extractReportOptions(Request $request): array
    {
        return [
            'tahun' => $request->input('tahun'),
            'keterangan' => $request->input('keterangan', "Surat Keputusan Bupati Malang\nNomor 100.3.3.2/401/35.07.013/2024\ntentang Data Statistik Sektoral Daerah\nKabupaten Malang Tahun 2025"),
            'tanggal_ttd' => $request->input('tanggal_ttd', 'Malang, Februari 2026'),
            'jabatan' => $request->input('jabatan', 'KEPALA DINAS KOMUNIKASI DAN INFORMATIKA'),
            'nama_ttd' => $request->input('nama_ttd', 'Drs. ATSALIS SUPRIYANTO, M.Si'),
            'pangkat_ttd' => $request->input('pangkat_ttd', 'Pembina Utama Muda'),
            'nip_ttd' => $request->input('nip_ttd', '196711301988091001'),
            'tahun_judul' => $request->input('tahun_judul', '2025'),
        ];
    }
}
