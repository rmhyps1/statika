@extends('layouts.app', ['title' => 'DSSD OPD'])

@section('content')
<div class="container-fluid p-0">
    <div class="row m-0" style="width: 100%; overflow-x: hidden;">
        <div class="col-12 mt-3">
            <section class="app-card mb-4" aria-labelledby="import-title">
            <header class="app-card-header">
                <div>
                    <span class="app-eyebrow">Impor Data</span>
                    <h2 id="import-title" class="app-card-title mt-1">Unggah Data DSSD (CSV/XLSX)</h2>
                </div>
                <span class="badge-status badge-status--secondary d-none d-sm-inline-block">Format: Kode DSSD, Uraian, Produsen, Ketersediaan</span>
            </header>
            <div class="app-card-body d-flex justify-content-between align-items-center">
                <p class="mb-0 text-muted">Upload file CSV/XLSX untuk menambahkan data DSSD ke dalam sistem.</p>
                <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#uploadDataModal">
                    Upload Data DSSD
                </button>
                <button type="button" class="btn-ghost ms-2" data-bs-toggle="modal" data-bs-target="#createDataModal">
                    + Tambah Manual
                </button>
            </div>
        </section>
    </div>

    @include("dssd-components.upload-modal")

    <div class="col-12 w-100 overflow-hidden">
        @if($hasImportedData)
        <div class="row mb-4 g-4">
            <div class="col-md-3 col-sm-6">
                <div class="app-card p-4 text-center" style="border-top: 4px solid var(--slate);">
                    <span class="app-eyebrow text-muted">Total Data</span>
                    <h3 class="display-6 fw-bold text-dark mt-2 mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="app-card p-4 text-center" style="border-top: 4px solid var(--primary); background-color: var(--primary-light);">
                    <span class="app-eyebrow" style="color: var(--primary);">Tersinkron (Cocok)</span>
                    <h3 class="display-6 fw-bold mt-2 mb-0" style="color: var(--primary);">{{ $stats['synced'] }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="app-card p-4 text-center" style="border-top: 4px solid #f59e0b; background-color: #fef3c7;">
                    <span class="app-eyebrow" style="color: #d97706;">Tidak Ditemukan</span>
                    <h3 class="display-6 fw-bold mt-2 mb-0" style="color: #d97706;">{{ $stats['not_found'] }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="app-card p-4 text-center" style="border-top: 4px solid var(--mist);">
                    <span class="app-eyebrow text-muted">Belum Sinkron</span>
                    <h3 class="display-6 fw-bold text-dark mt-2 mb-0">{{ $stats['not_synced'] }}</h3>
                </div>
            </div>
        </div>
        @endif

        <section class="app-card" aria-labelledby="table-title">
            <header class="app-card-header">
                <div>
                    <span class="app-eyebrow">Inventaris</span>
                    <h2 id="table-title" class="app-card-title mt-1">Daftar Data DSSD Terpadu</h2>
                </div>
                @if($hasImportedData)
                    <div class="d-flex flex-wrap gap-2 align-items-end">
                        <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#syncModal">Compare Data</button>
                        <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#exportModal">Ekspor Excel</button>
                        <button type="button" class="btn-ghost-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">Hapus Semua</button>
                    </div>
                @endif
            </header>

            <div class="app-card-body border-bottom">
                <form method="GET" action="{{ route('dssd') }}" class="dssd-filter-bar" aria-label="Filter Data DSSD">
                                        <div class="filter-group">
                        <label for="filter-bar-tahun" class="filter-label">Tahun</label>
                        <select name="tahun" id="filter-bar-tahun" class="form-select">
                            <option value="">Semua Tahun</option>
                            @php $currentYear = date('Y'); @endphp
                            @for($i = $currentYear - 3; $i <= $currentYear + 1; $i++)
                                <option value="{{ $i }}" @selected(request('tahun') == $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-bar-search" class="filter-label">Cari Uraian</label>
                        <input type="text" name="search" id="filter-bar-search" class="form-control" placeholder="Cari uraian…" value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <label for="filter-bar-produsen" class="filter-label">Produsen</label>
                        <select name="produsen_data" id="filter-bar-produsen" class="form-select">
                            <option value="">Semua Produsen</option>
                            @foreach($produsenDataOptions as $option)
                                <option value="{{ $option }}" @selected(request('produsen_data') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-bar-kategori" class="filter-label">Kategori</label>
                        <select name="kategori_data" id="filter-bar-kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="Sektoral" @selected(request('kategori_data') === 'Sektoral')>Sektoral</option>
                            <option value="Spasial" @selected(request('kategori_data') === 'Spasial')>Spasial</option>
                            <option value="e-Walidata" @selected(request('kategori_data') === 'e-Walidata')>e-Walidata</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-bar-jenis" class="filter-label">Jenis Data</label>
                        <select name="jenis_data" id="filter-bar-jenis" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="OPD" @selected(request('jenis_data') === 'OPD')>OPD</option>
                            <option value="Kecamatan" @selected(request('jenis_data') === 'Kecamatan')>Kecamatan</option>
                            <option value="Kelurahan" @selected(request('jenis_data') === 'Kelurahan')>Kelurahan</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-bar-kamasuta" class="filter-label">Status Kamasuta</label>
                        <select name="kamasuta_status" id="filter-bar-kamasuta" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="matched" @selected(request('kamasuta_status') === 'matched')>Ada di Kamasuta</option>
                            <option value="unmatched" @selected(request('kamasuta_status') === 'unmatched')>Tidak ada di Kamasuta</option>
                            <option value="not_synced" @selected(request('kamasuta_status') === 'not_synced')>Belum disinkronkan</option>
                            <option value="manual" @selected(request('kamasuta_status') === 'manual')>Diubah manual</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-primary-custom">Terapkan</button>
                        <a href="{{ route('dssd') }}" class="btn-ghost">Bersihkan</a>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                    <tr>
                        <th scope="col">Kode DSSD</th>
                        <th scope="col">Uraian DSSD</th>
                        <th scope="col">Produsen Data</th>
                        <th scope="col">Ketersediaan</th>
                        <th scope="col">Kategori</th>
                        <th scope="col" class="text-center">Jenis Data</th>
                        <th scope="col">Tahun</th>
                        <th scope="col" aria-label="Aksi">Aksi</th>
                    </tr>
                    <tr class="bg-light d-none">
                        <th></th>
                        <th>
                            <form method="GET" action="{{ route('dssd') }}" class="filter-group">
                                @foreach(request()->except('search', 'import_page') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label for="filter-search" class="filter-label visually-hidden">Cari Uraian</label>
                                <input type="text" name="search" id="filter-search" class="form-control form-control-sm" placeholder="Cari uraian…" value="{{ request('search') }}">
                                <button type="submit" class="d-none" aria-label="Terapkan pencarian"></button>
                            </form>
                        </th>
                        <th>
                            <form method="GET" action="{{ route('dssd') }}" class="filter-group">
                                @foreach(request()->except('produsen_data', 'import_page') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                @php
                                $produsenGroups = [
                                    'Badan & Inspektorat Daerah' => [
                                        'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia',
                                        'Badan Perencanaan dan Pembangunan Daerah',
                                        'Badan Riset dan Inovasi Daerah',
                                        'Badan Keuangan dan Aset Daerah',
                                        'Badan Pendapatan Daerah',
                                        'Badan Penanggulangan Bencana Daerah',
                                        'Badan Kesatuan Bangsa dan Politik',
                                        'Badan Penelitian dan Pengembangan Daerah',
                                        'Inspektorat Daerah'
                                    ],
                                    'Dinas Daerah' => [
                                        'Dinas Pendidikan',
                                        'Dinas Pemuda dan Olahraga',
                                        'Dinas Kesehatan',
                                        'Dinas Sosial',
                                        'Dinas Tenaga Kerja',
                                        'Dinas Perhubungan',
                                        'Dinas Kependudukan dan Pencatatan Sipil',
                                        'Dinas Pariwisata dan Kebudayaan',
                                        'Dinas PU Bina Marga',
                                        'Dinas PU Sumber Daya Air',
                                        'Dinas Perumahan Kawasan Permukiman dan Cipta Karya',
                                        'Dinas Koperasi dan Usaha Mikro',
                                        'Dinas Perindustrian dan Perdagangan',
                                        'Dinas Tanaman Pangan Holtikulturan dan Perkebunan',
                                        'Dinas Perikanan',
                                        'Dinas Ketahanan Pangan',
                                        'Dinas Lingkungan Hidup',
                                        'Dinas Peternakan dan Kesehatan Hewan',
                                        'Dinas Pemberdayaan Masyarakat dan Desa',
                                        'Dinas Pengendalian Penduduk dan Keluarga Berencana',
                                        'Dinas Perpustakaan dan Kearsipan',
                                        'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
                                        'Dinas Pemberdayaan Perempuan dan Protection Anak',
                                        'Dinas Komunikasi dan Informatika',
                                        'Dinas Pertanahan'
                                    ],
                                    'Sekretariat, Satuan, & RSUD' => [
                                        'Sekretariat DPRD',
                                        'Satuan Polisi Pamong Praja',
                                        'RSUD Kanjuruhan',
                                        'RSUD Lawang',
                                        'RSUD Ngantang'
                                    ],
                                    'Bagian Sekretariat Daerah' => [
                                        'Bagian Tata Pemerintahan',
                                        'Bagian Kesejahteraan Rakyat',
                                        'Bagian Hukum',
                                        'Bagian Perekonomian',
                                        'Bagian Kerjasama',
                                        'Bagian Pengadaan Barang dan Jasa',
                                        'Bagian Sumber Daya Alam',
                                        'Bagian Umum',
                                        'Bagian Protokol Dan Komunikasi Pimpinan',
                                        'Bagian Organisasi',
                                        'Bagian Perencanaan dan Keuangan',
                                        'Bagian Administrasi Pembangunan'
                                    ],
                                    'BUMD & Lembaga Lainnya' => [
                                        'Perusahaan Umum Daerah Tirta Kanjuruhan',
                                        'Perumda Jasa Yasa',
                                        'BPR Artha Kanjuruhan',
                                        'Palang Merah Indonesia Kabupaten Malang',
                                        'Cabang Dinas Pendidikan Wilayah Kabupaten Malang'
                                    ],
                                    'Kecamatan' => [
                                        'Kecamatan Ampelgading', 'Kecamatan Bantur', 'Kecamatan Bululawang',
                                        'Kecamatan Dampit', 'Kecamatan Dau', 'Kecamatan Donomulyo',
                                        'Kecamatan Gedangan', 'Kecamatan Gondanglegi', 'Kecamatan Jabung',
                                        'Kecamatan Kalipare', 'Kecamatan Karangploso', 'Kecamatan Kasembon',
                                        'Kecamatan Kepanjen', 'Kecamatan Kromengan', 'Kecamatan Lawang',
                                        'Kecamatan Ngajum', 'Kecamatan Ngantang', 'Kecamatan Pagak',
                                        'Kecamatan Pagelaran', 'Kecamatan Pakis', 'Kecamatan Pakisaji',
                                        'Kecamatan Poncokusumo', 'Kecamatan Pujon', 'Kecamatan Singosari',
                                        'Kecamatan Sumbermanjing Wetan', 'Kecamatan Sumberpucung',
                                        'Kecamatan Tajinan', 'Kecamatan Tirtoyudo', 'Kecamatan Tumpang',
                                        'Kecamatan Turen', 'Kecamatan Wagir', 'Kecamatan Wajak', 'Kecamatan Wonosari'
                                    ],
                                    'Kelurahan' => [
                                        'Kelurahan Dampit', 'Kelurahan Sedayu', 'Kelurahan Turen',
                                        'Kelurahan Penarukan', 'Kelurahan Cepokomulyo', 'Kelurahan Kepanjen',
                                        'Kelurahan Ardirejo', 'Kelurahan Candirenggo', 'Kelurahan Pagentan',
                                        'Kelurahan Losari', 'Kelurahan Kalirejo', 'Kelurahan Lawang'
                                    ]
                                ];
                                @endphp
                                <label for="filter-produsen" class="filter-label visually-hidden">Filter Produsen</label>
                                <select name="produsen_data" id="filter-produsen" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Produsen</option>
                                    @foreach($produsenGroups as $groupName => $options)
                                        <optgroup label="{{ $groupName }}">
                                            @foreach($options as $option)
                                                <option value="{{ $option }}" @selected(request('produsen_data') === $option)>{{ $option }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </form>
                        </th>
                        <th></th>
                        <th>
                            <form method="GET" action="{{ route('dssd') }}" class="filter-group">
                                @foreach(request()->except('kategori_data', 'import_page') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label for="filter-kategori" class="filter-label visually-hidden">Filter Kategori</label>
                                <select name="kategori_data" id="filter-kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Kategori</option>
                                    <option value="Sektoral" @selected(request('kategori_data') === 'Sektoral')>Sektoral</option>
                                    <option value="Spasial" @selected(request('kategori_data') === 'Spasial')>Spasial</option>
                                    <option value="e-Walidata" @selected(request('kategori_data') === 'e-Walidata')>e-Walidata</option>
                                </select>
                            </form>
                        </th>
                        <th>
                            <form method="GET" action="{{ route('dssd') }}" class="filter-group">
                                @foreach(request()->except('jenis_data', 'import_page') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label for="filter-jenis" class="filter-label visually-hidden">Filter Jenis Data</label>
                                <select name="jenis_data" id="filter-jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Jenis</option>
                                    <option value="OPD" @selected(request('jenis_data') === 'OPD')>OPD</option>
                                    <option value="Kecamatan" @selected(request('jenis_data') === 'Kecamatan')>Kecamatan</option>
                                    <option value="Kelurahan" @selected(request('jenis_data') === 'Kelurahan')>Kelurahan</option>
                                </select>
                            </form>
                        </th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($importedData as $item)
                        <tr>
                            <td class="table-code">{{ $item->kode_dssd }}</td>
                            <td class="fw-medium">{{ $item->uraian_dssd }}</td>
                            <td>{{ $item->produsen_data }}</td>
                            <td>
                                <div class="availability-cell">
                                    <form action="{{ route('imported-dssd-data.availability', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <label for="ketersediaan-{{ $item->id }}" class="visually-hidden">Ubah ketersediaan untuk {{ $item->uraian_dssd }}</label>
                                        <select name="ketersediaan_data" id="ketersediaan-{{ $item->id }}" class="form-select form-select-sm availability-select" onchange="this.form.submit()">
                                            <option value="ada" @selected($item->ketersediaan_data === 'ada')>Ada</option>
                                            <option value="tidak" @selected($item->ketersediaan_data === 'tidak')>Tidak</option>
                                        </select>
                                    </form>
                                    <span class="badge-status {{ $item->status_badge_class }} availability-badge">{{ $item->status_label }}</span>
                                </div>
                            </td>
                            <td class="category-cell">
                                <span class="badge-status {{ $item->kategori_badge_class }} category-badge">{{ $item->kategori_label }}</span>
                            </td>
                            <td class="text-center">{{ $item->jenis_data }}</td>
                            <td class="table-year">{{ $item->tahun }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn-detail-toggle" aria-expanded="false" aria-controls="import-child-{{ $item->id }}">Lihat Detail</button>
                                    <button type="button" class="btn-ghost btn-sm-custom" data-bs-toggle="modal" data-bs-target="#editImport{{ $item->id }}" aria-label="Edit {{ $item->uraian_dssd }}">Edit</button>
                                    <button type="button" 
                                            class="btn-ghost-danger btn-sm-custom" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteItemModal"
                                            onclick="setDeleteModal('{{ route('imported-dssd-data.destroy', $item) }}', @js($item->uraian_dssd))">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr id="import-child-{{ $item->id }}" class="table-child">
                            <td colspan="8" class="p-0 border-0">
                                <div class="child-detail mx-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="app-eyebrow mb-0">Informasi Lengkap</span>
                                        <span class="badge-status badge-status--secondary badge-code">{{ $item->kode_dssd }}</span>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-sm-6 col-md-3">
                                            <div class="detail-label">Satuan</div>
                                            <div class="text-ink">{{ $item->satuan ?? $item->raw_data['satuan'] ?? '-' }}</div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="detail-label">Tag Urusan</div>
                                            <div class="text-ink">{{ $item->tag_urusan ?? $item->raw_data['tag urusan'] ?? '-' }}</div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="detail-label">Info Sub Kegiatan</div>
                                            <div class="text-ink">{{ $item->info_sub_kegiatan ?? $item->raw_data['info sub kegiatan'] ?? '-' }}</div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="detail-label">Keterangan</div>
                                            <div class="text-ink">{{ $item->keterangan ?? $item->raw_data['keterangan'] ?? '-' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="detail-label">Definisi Operasional</div>
                                            <div class="text-ink bg-white p-3 border rounded mt-1">{{ $item->definisi_operasional ?? $item->raw_data['definisi operasional'] ?? 'Tidak ada definisi operasional yang diberikan.' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-0">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-state-icon" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <h3 class="empty-state-title">Belum Ada Data DSSD</h3>
                                    <p class="empty-state-desc">Silakan pilih dan import file CSV atau XLSX melalui form di atas untuk mulai mengelola data statistik sektoral.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="app-card-footer">
                {{ $importedData->links() }}
            </footer>
        </section>
    </div>
</div>

@include("dssd-components.sync-modal")

@include("dssd-components.export-modal")

@include("dssd-components.delete-all-modal")

@include("dssd-components.export-loading-modal")

@foreach($importedData as $item)
<div class="modal fade" id="editImport{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" action="{{ route('imported-dssd-data.update', $item) }}" method="POST">
            @csrf
            @method('PUT')
            <header class="modal-header">
                <h2 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Data DSSD</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup form edit"></button>
            </header>
            <div class="modal-body">
                <div class="row g-4">
                    @include('partials.imported-dssd-form', ['item' => $item])
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn-primary-custom">Simpan Perubahan</button>
            </footer>
        </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
    let selectedFilesData = [];

    function handleFileSelect(input) {
        if (!input.files || input.files.length === 0) return;

        const maxSize = 40 * 1024 * 1024;
        const allowedExts = ['csv', 'txt', 'xlsx', 'xls'];
        
        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i];
            
            if (file.size > maxSize) {
                alert('Gagal! File "' + file.name + '" melebihi batas maksimal (40MB).');
                input.value = '';
                return;
            }
            
            let ext = file.name.split('.').pop().toLowerCase();
            if (!allowedExts.includes(ext)) {
                alert('Gagal! File "' + file.name + '" memiliki format tidak diizinkan. Hanya boleh CSV atau XLSX.');
                input.value = '';
                return;
            }
        }

        var progressContainer = document.getElementById('uploadProgressContainer');
        var hiddenInputsContainer = document.getElementById('hiddenFileInputs');
        progressContainer.classList.remove('d-none');
        document.getElementById('btnSubmitUpload').disabled = true;

        var hiddenInput = input.cloneNode();
        hiddenInput.removeAttribute('id');
        hiddenInput.className = 'd-none file-group-input';
        hiddenInput.name = 'file[]';
        hiddenInputsContainer.appendChild(hiddenInput);

        const dataTransfer = new DataTransfer();
        Array.from(input.files).forEach(f => dataTransfer.items.add(f));
        hiddenInput.files = dataTransfer.files;

        input.value = '';

        let fileGroupId = 'fg_' + Date.now();
        hiddenInput.setAttribute('data-fg', fileGroupId);
        
        Array.from(dataTransfer.files).forEach((file, index) => {
            selectedFilesData.push({
                groupId: fileGroupId,
                fileIndex: index,
                name: file.name,
                size: file.size,
                progress: 0,
                status: 'reading'
            });
        });

        renderFileList();
        simulateProgressForNewFiles(fileGroupId);
    }

    function removeFile(groupId, fileIndex) {
        selectedFilesData = selectedFilesData.filter(f => !(f.groupId === groupId && f.fileIndex === fileIndex));
        
        let hiddenInput = document.querySelector(`.file-group-input[data-fg="${groupId}"]`);
        if (hiddenInput) {
            const dt = new DataTransfer();
            Array.from(hiddenInput.files).forEach((f, i) => {
                if (i !== fileIndex) dt.items.add(f);
            });
            hiddenInput.files = dt.files;
            
            if (hiddenInput.files.length === 0) {
                hiddenInput.remove();
            }
        }
        
        renderFileList();
        checkAllFilesReady();
        
        if (selectedFilesData.length === 0) {
            document.getElementById('uploadProgressContainer').classList.add('d-none');
        }
    }

    function renderFileList() {
        var fileList = document.getElementById('selectedFileList');
        fileList.innerHTML = '';
        
        selectedFilesData.forEach(file => {
            let li = document.createElement('li');
            li.className = "mb-3 p-3 border rounded bg-light";
            
            let id = `progress-${file.groupId}-${file.fileIndex}`;
            let textId = `text-progress-${file.groupId}-${file.fileIndex}`;
            
            let statusBadge = file.status === 'ready' 
                ? '<span class="text-success ms-2 fw-bold small">100% Siap</span>' 
                : `<span class="text-primary ms-2 fw-bold small" id="${textId}">${file.progress}%</span>`;
                
            let progressBarHtml = file.status === 'ready'
                ? `<div class="progress" style="height: 6px;"><div class="progress-bar bg-success" style="width: 100%;"></div></div>`
                : `<div class="progress" style="height: 6px;"><div id="${id}" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: ${file.progress}%;"></div></div>`;
            
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center text-truncate pe-3">
                        <span class="fs-5 me-2">📄</span>
                        <div class="text-truncate">
                            <strong>${file.name}</strong> 
                            <span class="text-muted small ms-1">(${(file.size/1024).toFixed(1)} KB)</span>
                            ${statusBadge}
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" onclick="removeFile('${file.groupId}', ${file.fileIndex})" title="Hapus file ini">✖</button>
                </div>
                ${progressBarHtml}
            `;
            fileList.appendChild(li);
        });
    }

    function simulateProgressForNewFiles(groupId) {
        let filesInProgress = selectedFilesData.filter(f => f.groupId === groupId);
        if (filesInProgress.length === 0) return;
        
        var interval = setInterval(function() {
            let allDone = true;
            
            filesInProgress.forEach(file => {
                if (file.status === 'reading') {
                    file.progress += Math.floor(Math.random() * 15) + 5; 
                    
                    if (file.progress >= 100) {
                        file.progress = 100;
                        file.status = 'ready';
                    } else {
                        allDone = false;
                    }
                    
                    let bar = document.getElementById(`progress-${file.groupId}-${file.fileIndex}`);
                    let text = document.getElementById(`text-progress-${file.groupId}-${file.fileIndex}`);
                    
                    if (bar) bar.style.width = file.progress + '%';
                    if (text) text.innerText = file.progress + '%';
                }
            });
            
            if (allDone) {
                clearInterval(interval);
                renderFileList(); 
                checkAllFilesReady();
            }
        }, 150);
    }
    
    function checkAllFilesReady() {
        var btnSubmit = document.getElementById('btnSubmitUpload');
        
        if (selectedFilesData.length === 0) {
            btnSubmit.disabled = true;
            return;
        }
        
        let allReady = selectedFilesData.every(f => f.status === 'ready');
        btnSubmit.disabled = !allReady;
    }

    // === Penguncian Modal Saat Loading ===
    let isModalProcessing = false;

    // Cegah modal ditutup saat sedang loading
    ['uploadDataModal', 'syncModal', 'deleteItemModal', 'deleteAllModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('hide.bs.modal', function (e) {
            if (isModalProcessing) e.preventDefault();
        });
    });

    // Fitur Loading State untuk Sinkronisasi
    function showSyncLoading(form) {
        isModalProcessing = true;
        document.getElementById('syncModalContentHome').classList.add('d-none');
        document.getElementById('syncModalLoadingHome').classList.remove('d-none');
        var submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    // Fitur Loading State untuk Upload
    function showUploadLoading(form) {
        isModalProcessing = true;
        document.getElementById('uploadModalContent').classList.add('d-none');
        document.getElementById('uploadModalLoading').classList.remove('d-none');
        var submitBtn = document.getElementById('btnSubmitUpload');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    // Fitur Loading State untuk Hapus Item
    function showDeleteLoading(form) {
        isModalProcessing = true;
        document.getElementById('deleteItemModalContent').classList.add('d-none');
        document.getElementById('deleteItemModalLoading').classList.remove('d-none');
        var submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    // Fitur Loading State untuk Hapus Semua
    function showDeleteAllLoading(form) {
        isModalProcessing = true;
        document.getElementById('deleteAllModalContent').classList.add('d-none');
        document.getElementById('deleteAllModalLoading').classList.remove('d-none');
        var submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    // FUNGSI UNTUK MODAL HAPUS PER ITEM
    function setDeleteModal(url, uraian) {
        document.getElementById('deleteItemForm').action = url;
        // Batasi panjang uraian jika terlalu panjang agar modal tidak penuh
        var truncatedUraian = uraian.length > 150 ? uraian.substring(0, 150) + '...' : uraian;
        document.getElementById('deleteItemUraian').textContent = truncatedUraian;
    }
</script>
@endpush

<!-- Modal Hapus Item -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteItemForm" action="" method="POST" class="modal-content" onsubmit="showDeleteLoading(this)">
            @csrf
            @method('DELETE')
            <div id="deleteItemModalContent">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteItemModalLabel">Hapus Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data ini?</p>
                    <div class="p-3 bg-light rounded border border-light">
                        <p class="fw-medium mb-0 text-dark" id="deleteItemUraian" style="font-size: 0.875rem; word-break: break-word;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus Data</button>
                </div>
            </div>
            
            <!-- Loading State Hapus Item -->
            <div class="modal-content d-none" id="deleteItemModalLoading">
                <div class="modal-body text-center py-5">
                    <div class="export-spinner mb-4"></div>
                    <h3 class="app-card-title mb-2">Menghapus Data...</h3>
                    <p class="text-muted mb-0">Mohon tunggu, data sedang dihapus dari database.</p>
                </div>
            </div>
        </form>
    </div>
</div>

@include("dssd-components.create-manual-modal")
