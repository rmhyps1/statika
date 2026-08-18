@extends('layouts.app', ['title' => 'Kamasuta API Explorer'])

@section('content')
<div class="row g-4">
    <!-- Overlay Loading Sync -->
    <div id="syncLoadingOverlay" class="d-none align-items-center justify-content-center" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 9999;">
        <div class="bg-white p-4 rounded text-center shadow" style="min-width: 300px;">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="mb-2">Menyinkronkan Data</h5>
            <p class="text-muted small mb-0">Mohon tunggu, proses ini mengambil data dari API Kamasuta...</p>
        </div>
    </div>

    @if(isset($totalDataCard))
    <div class="col-12 col-md-4 mb-3">
        <div class="app-card p-4 text-center" style="border-top: 4px solid var(--primary); background-color: var(--primary-light);">
            <span class="app-eyebrow" style="color: var(--primary);">TOTAL DATA</span>
            <h3 class="display-6 fw-bold mt-2 mb-0" style="color: var(--primary);">{{ number_format($totalDataCard, 0, ',', '.') }}</h3>
        </div>
    </div>
    @endif

    <!-- Data Table Section -->
    <div class="col-12">
        <section class="app-card" aria-labelledby="kamasuta-title">
            <header class="app-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <span class="app-eyebrow">API Explorer</span>
                    <h2 id="kamasuta-title" class="app-card-title mt-1">{{ $title ?? 'Data Kamasuta' }}</h2>
                </div>
                <div>
                    <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#syncConfirmModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat me-1" viewBox="0 0 16 16" style="vertical-align: text-bottom;">
                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                        </svg>
                        Sinkronkan Ulang Kamasuta
                    </button>
                </div>
            </header>
            
            <div class="app-card-body p-0">
                @if(isset($errorMessage))
                    <div class="p-4">
                        <div class="alert alert-danger" style="border-radius: 0.5rem;">{{ $errorMessage }}</div>
                    </div>
                @elseif(isset($apiData) && is_array($apiData) && (count($apiData) > 0 || $activeTab == 'judul-list'))
                    <div class="w-100" style="overflow-x: auto;">
                        
                        @if($activeTab == 'judul-detail' || $activeTab == 'parameter-detail')
                            <!-- Detail View (Rendered generic table or custom cards) -->
                            <div class="p-4">
                                <a href="{{ route('kamasuta') }}" class="btn-ghost mb-4">&laquo; Kembali ke Daftar</a>
                                <div class="table-responsive" style="border: 1px solid var(--mist); border-radius: var(--radius);">
                                    <table class="table app-table mb-0">
                                        <tbody>
                                            @foreach($apiData[0] ?? [] as $key => $value)
                                                <tr>
                                                    <th class="w-25 bg-light" style="color: var(--ink-secondary); font-weight: 600;">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                                    <td>
                                                        @if(is_array($value) || is_object($value))
                                                            <pre class="m-0 p-3 bg-light rounded text-muted table-code">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <!-- Filter Bar (Moved outside table) -->
                            <div class="kamasuta-filter-shell border-bottom">
                                <div class="kamasuta-filter-bar">
                                    <form method="GET" action="{{ route('kamasuta') }}" class="filter-group kamasuta-search-group">
                                        @foreach(request()->except('search', 'page') as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        <label for="filter-bar-search" class="filter-label">Cari Judul Data</label>
                                        <div class="kamasuta-search-field">
                                            <input type="text" name="search" id="filter-bar-search" class="form-control" placeholder="Contoh: penduduk miskin" value="{{ request('search') }}">
                                            <button type="submit" class="kamasuta-search-button" title="Cari judul data">
                                                Cari
                                            </button>
                                        </div>
                                    </form>

                                    <?php $opdParam = $filterOptions['opd_param'] ?? 'opd_id'; ?>
                                    <form method="GET" action="{{ route('kamasuta') }}" class="filter-group">
                                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                                        @if(request('jenis_data'))<input type="hidden" name="jenis_data" value="{{ request('jenis_data') }}">@endif
                                        @if(request('tahun'))<input type="hidden" name="tahun" value="{{ request('tahun') }}">@endif
                                        <label class="filter-label">OPD</label>
                                        <select name="{{ $opdParam }}" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua OPD</option>
                                            @foreach(($filterOptions['opd'] ?? []) as $opdId => $opd)
                                                <option value="{{ $opdId }}" {{ request($opdParam) == $opdId ? 'selected' : '' }}>{{ $opd }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    
                                    <form method="GET" action="{{ route('kamasuta') }}" class="filter-group">
                                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                                        @if(request($opdParam))<input type="hidden" name="{{ $opdParam }}" value="{{ request($opdParam) }}">@endif
                                        @if(request('tahun'))<input type="hidden" name="tahun" value="{{ request('tahun') }}">@endif
                                        <label class="filter-label">Jenis Data</label>
                                        <select name="jenis_data" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Jenis Data</option>
                                            @foreach(($filterOptions['jenis_data'] ?? []) as $slug => $jenis)
                                                <option value="{{ $slug }}" {{ request('jenis_data') === $slug ? 'selected' : '' }}>{{ $jenis }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    
                                    <form method="GET" action="{{ route('kamasuta') }}" class="filter-group">
                                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                                        @if(request('jenis_data'))<input type="hidden" name="jenis_data" value="{{ request('jenis_data') }}">@endif
                                        @if(request($opdParam))<input type="hidden" name="{{ $opdParam }}" value="{{ request($opdParam) }}">@endif
                                        <label class="filter-label">Tahun</label>
                                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Tahun</option>
                                            @php 
                                                $currentYear = date('Y'); 
                                                $startYear = 2018;
                                                $endYear = $currentYear + 1;
                                            @endphp
                                            @for($i = $endYear; $i >= $startYear; $i--)
                                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </form>
                                    
                                    @if(request('search') || request('jenis_data') || request($opdParam) || request('tahun'))
                                    <div class="filter-group kamasuta-reset-group">
                                        <a href="{{ route('kamasuta') }}" class="filter-reset-link" title="Hapus semua filter">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                              <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                            </svg>
                                            Reset
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Custom Table Layout matching Kamasuta -->
                            <div class="table-responsive">
                                <table class="table app-table mb-0">
                                    @php
                                        $sortUrl = function($column) {
                                            $currentSort = request('sort_by');
                                            $currentDir = request('sort_dir', 'asc');
                                            $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
                                            return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir, 'page' => 1]);
                                        };
                                        $sortIcon = function($column) {
                                            if (request('sort_by') !== $column) {
                                                return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-down-up ms-1 text-black-50" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5zm-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/></svg>';
                                            }
                                            return request('sort_dir', 'asc') === 'asc' 
                                                ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-up ms-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/></svg>' 
                                                : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-down ms-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L7.5 13.293V1.5A.5.5 0 0 1 8 1z"/></svg>';
                                        };
                                    @endphp
                                    <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%">No</th>
                                        <th style="width: 15%">
                                            <a href="{{ $sortUrl('kode') }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                Kode Data {!! $sortIcon('kode') !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ $sortUrl('judul') }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                Nama Judul Data {!! $sortIcon('judul') !!}
                                            </a>
                                        </th>
                                        <th style="width: 25%">
                                            <a href="{{ $sortUrl('opd') }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                OPD {!! $sortIcon('opd') !!}
                                            </a>
                                        </th>
                                        <th class="text-center" style="width: 10%">
                                            <a href="{{ $sortUrl('jenis_data') }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                                                Jenis Data {!! $sortIcon('jenis_data') !!}
                                            </a>
                                        </th>
                                        <th class="text-center" style="width: 10%">
                                            <a href="{{ $sortUrl('tahun') }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                                                Tahun {!! $sortIcon('tahun') !!}
                                            </a>
                                        </th>
                                        <th class="text-center" style="width: 10%">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($apiData as $key => $item)
                                        <tr>
                                            <td class="text-center text-muted table-code">{{ (isset($pagination) ? ($pagination['current_page'] - 1) * $pagination['per_page'] : 0) + $loop->iteration }}</td>
                                            <td class="table-code">
                                                {{ is_scalar($item['kode'] ?? $item['kode_dssd'] ?? $item['kode_data'] ?? '-') ? ($item['kode'] ?? $item['kode_dssd'] ?? $item['kode_data'] ?? '-') : (is_array($item['kode'] ?? $item['kode_dssd'] ?? $item['kode_data']) ? json_encode($item['kode'] ?? $item['kode_dssd'] ?? $item['kode_data']) : '-') }}
                                            </td>
                                            <td class="fw-medium">
                                                {{ is_scalar($item['judul'] ?? $item['nama_judul_data'] ?? $item['uraian_dssd'] ?? '-') ? ($item['judul'] ?? $item['nama_judul_data'] ?? $item['uraian_dssd'] ?? '-') : (is_array($item['judul'] ?? $item['nama_judul_data'] ?? $item['uraian_dssd']) ? json_encode($item['judul'] ?? $item['nama_judul_data'] ?? $item['uraian_dssd']) : '-') }}
                                            </td>
                                            <td class="text-muted text-sm">
                                                {{ $item['_opd_label'] ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-status badge-status--info category-badge">
                                                    {{ $item['_jenis_data_label'] ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-center table-year">
                                                {{ is_scalar($item['tahun'] ?? '-') ? ($item['tahun'] ?? '-') : (is_array($item['tahun']) ? json_encode($item['tahun']) : '-') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @if(isset($item['judul_id']))
                                                        <a href="{{ route('kamasuta.judul-detail', ['id' => $item['judul_id']]) }}" class="btn-ghost btn-sm-custom" title="Lihat Detail" aria-label="Lihat detail">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg>
                                                        </a>
                                                    @elseif(!empty($item['_detail_url']))
                                                        <a href="{{ $item['_detail_url'] }}" target="_blank" rel="noopener" class="btn-ghost btn-sm-custom" title="Lihat Detail" aria-label="Lihat detail">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-0">
                                                <div class="empty-state">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-state-icon" aria-hidden="true">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4H4.98zm-1.17-.437A1.5 1.5 0 0 1 4.98 3h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a.5.5 0 0 1 .106.311l-.001 5.5a1.5 1.5 0 0 1-1.5 1.5H1.5A1.5 1.5 0 0 1 0 14.5v-5.5a.5.5 0 0 1 .106-.31l3.7-4.625zM1 14.5A.5.5 0 0 0 1.5 15h13a.5.5 0 0 0 .5-.5V8.5H11a.5.5 0 0 1-.5.5 2.5 2.5 0 0 1-5 0 .5.5 0 0 1-.5-.5H1v6z"/>
                                                    </svg>
                                                    <h3 class="empty-state-title">Data tidak ditemukan</h3>
                                                    <p class="empty-state-desc">Tidak ada data yang dikembalikan oleh API untuk pencarian/kategori ini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                </table>
                            </div>
                        @endif
                        
                        <!-- Pagination UI -->
                        @if(isset($pagination) && $pagination['last_page'] > 1)
                            <footer class="app-card-footer d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    Menampilkan Halaman <strong>{{ $pagination['current_page'] }}</strong> dari <strong>{{ $pagination['last_page'] }}</strong> 
                                    <span class="mx-2">|</span> Total: <strong>{{ number_format($pagination['total'], 0, ',', '.') }}</strong> data
                                </span>
                                <div class="btn-group shadow-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['page' => max(1, $pagination['current_page'] - 1)]) }}" class="btn btn-sm btn-outline-secondary {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}">
                                        &laquo; Prev
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['page' => min($pagination['last_page'], $pagination['current_page'] + 1)]) }}" class="btn btn-sm btn-outline-secondary {{ $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' }}">
                                        Next &raquo;
                                    </a>
                                </div>
                            </footer>
                        @endif
                        
                    </div>
                @else
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-state-icon" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                        </svg>
                        <h3 class="empty-state-title">Data Kosong</h3>
                        <p class="empty-state-desc">Tidak ada data yang dikembalikan oleh API untuk pencarian/kategori ini.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<!-- Modal Konfirmasi Sinkronisasi -->
<div class="modal fade" id="syncConfirmModal" tabindex="-1" aria-labelledby="syncConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <header class="modal-header">
                <h2 class="modal-title" id="syncConfirmModalLabel">Sinkronisasi Data Kamasuta</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </header>
            <div class="modal-body">
                <p class="mb-0">Proses sinkronisasi data dari API Kamasuta mungkin membutuhkan waktu agak lama. Apakah Anda yakin ingin melanjutkan?</p>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-primary-custom" id="btnConfirmSyncAction" data-bs-dismiss="modal">Ya, Sinkronkan</button>
            </footer>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSync = document.getElementById('btnSyncKamasuta');
    const btnConfirm = document.getElementById('btnConfirmSyncAction');
    const overlay = document.getElementById('syncLoadingOverlay');
    const overlayBox = document.querySelector('#syncLoadingOverlay .bg-white');
    const originalOverlayHtml = overlayBox ? overlayBox.innerHTML : '';

    if (btnConfirm && btnSync) {
        btnConfirm.addEventListener('click', function(e) {
            e.preventDefault();
            
            if(overlay) {
                // Restore loading animation in case it was changed by previous error
                if (overlayBox && originalOverlayHtml) {
                    overlayBox.innerHTML = originalOverlayHtml;
                }
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            }
            
            const originalText = btnSync.innerHTML;
            btnSync.disabled = true;
            
            fetch("{{ route('kamasuta.sync') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tahun: '{{ request("tahun") ?? date("Y") }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    checkSyncStatus(data.job_id, originalText);
                } else {
                    showErrorOverlay('Gagal memulai sinkronisasi: ' + (data.message || 'Unknown error'));
                    btnSync.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorOverlay('Terjadi kesalahan koneksi saat memulai sinkronisasi.');
                btnSync.disabled = false;
            });
        });
    }
    
    function hideOverlay() {
        if(overlay) {
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');
        }
    }

    function showErrorOverlay(message) {
        if (overlayBox) {
            overlayBox.innerHTML = `
                <div class="text-danger mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                </div>
                <h5 class="mb-2 text-danger">Sinkronisasi Gagal</h5>
                <p class="text-muted small mb-4">${message}</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('syncLoadingOverlay').classList.add('d-none'); document.getElementById('syncLoadingOverlay').classList.remove('d-flex');">Tutup</button>
            `;
        }
    }
    
    function checkSyncStatus(jobId, originalText) {
        const checkInterval = setInterval(function() {
            fetch(`/api-sync/status/${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(checkInterval);
                        
                        if (overlayBox) {
                            overlayBox.innerHTML = `
                                <div class="text-success mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                </div>
                                <h5 class="mb-2">Sinkronisasi Selesai</h5>
                                <p class="text-muted small mb-0">Halaman akan dimuat ulang...</p>
                            `;
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else if (data.status === 'failed' || data.status === 'not_found') {
                        clearInterval(checkInterval);
                        const btn = document.getElementById('btnSyncKamasuta');
                        if(btn) btn.disabled = false;
                        showErrorOverlay('Sinkronisasi gagal atau dihentikan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                    // Biarkan terus mengecek sampai batas waktu (atau biarkan jalan)
                    // Kalau mau distop:
                    // clearInterval(checkInterval);
                    // showErrorOverlay('Gagal mengecek status sinkronisasi.');
                });
        }, 2000);
    }
});
</script>
@endpush
