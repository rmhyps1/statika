@extends('layouts.app', ['title' => 'Laporan LPPD'])

@section('content')
<div class="container-fluid p-0">
    <!-- Tambahan margin-top khusus untuk komponen pertama di dalam main agar tidak tertutup header -->
    <div class="col-12 mt-3">
        <section class="app-card mb-4" aria-labelledby="laporan-title">
            <header class="app-card-header">
                <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="app-eyebrow">Manajemen Data</span>
                        <h2 id="laporan-title" class="app-card-title mt-1">LPPD Ketersediaan Data</h2>
                    </div>
                    <div class="filter-actions d-flex align-items-center gap-2">
                        <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#tambahSpasialModal">
                            + Tambah Data Spasial
                        </button>
                        <form method="GET" action="{{ route('laporan.index') }}" class="d-inline-block">
                            <label class="visually-hidden" for="filter-tahun">Pilih Tahun</label>
                            <select name="tahun" id="filter-tahun" class="form-select d-inline-block w-auto" onchange="if(!this.value) { window.location.href = '{{ route('laporan.index') }}'; } else { this.form.submit(); }" style="cursor: pointer; min-width: 140px;">
                                <option value="">Semua Tahun</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $tahunFilter == $year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div class="dropdown d-inline-block">
                            <button class="btn-ghost dropdown-toggle" type="button" id="dropdownDownload" data-bs-toggle="dropdown" aria-expanded="false">
                                Download Docx
                            </button>
                            <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="dropdownDownload">
                                <li><h6 class="dropdown-header">Template Kosong</h6></li>
                                <li><a class="dropdown-item" href="#" onclick="openDownloadModal('{{ route('laporan.download.template.dilaporkan') }}', 'Template Jumlah Dilaporkan', false)">Template Jumlah Dilaporkan</a></li>
                                <li><a class="dropdown-item" href="#" onclick="openDownloadModal('{{ route('laporan.download.template.disepakati') }}', 'Template Jumlah Disepakati', false)">Template Jumlah Disepakati</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Hasil Rekapitulasi</h6></li>
                                <li><a class="dropdown-item" href="#" onclick="openDownloadModal('{{ route('laporan.download') }}', 'Laporan Persentase', false)">Laporan Persentase</a></li>
                                <li><a class="dropdown-item" href="#" onclick="openDownloadModal('{{ route('laporan.download.dilaporkan') }}', 'Rekap Jumlah Dilaporkan', true)">Rekap Jumlah Dilaporkan</a></li>
                                <li><a class="dropdown-item" href="#" onclick="openDownloadModal('{{ route('laporan.download.disepakati') }}', 'Rekap Jumlah Disepakati', true)">Rekap Jumlah Disepakati</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="app-card-body">
                <div class="row mb-5 g-4">
                    <div class="col-md-3">
                        <div class="app-card p-4 text-center" style="border-top: 4px solid var(--slate);">
                            <span class="app-eyebrow text-muted">Total Disepakati</span>
                            <h3 class="display-5 fw-bold text-dark mt-2 mb-0" id="totalDisepakati">{{ $totalDisepakati }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="app-card p-4 text-center" style="border-top: 4px solid var(--slate);">
                            <span class="app-eyebrow text-muted">Total Data Kamasuta</span>
                            <h3 class="display-5 fw-bold text-dark mt-2 mb-0" id="totalKamasuta">{{ $totalKamasuta }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="app-card p-4 text-center" style="border-top: 4px solid var(--slate);">
                            <span class="app-eyebrow text-muted">Total Data Spasial</span>
                            <h3 class="display-5 fw-bold text-dark mt-2 mb-0" id="totalSpasial">{{ $totalSpasial }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="app-card p-4 text-center" style="border-top: 4px solid var(--primary); background-color: var(--primary-light);">
                            <span class="app-eyebrow" style="color: var(--primary);">Persentase Capaian</span>
                            <h3 class="display-5 fw-bold mt-2 mb-0" style="color: var(--primary);" id="persentaseCapaian">{{ $persentase }}%</h3>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success py-2 mb-4" style="font-size: 0.875rem;">
                    {{ session('success') }}
                </div>
                @endif

                @if($tanpaProdusenCount > 0)
                <div class="alert alert-warning py-2 mb-4" style="font-size: 0.875rem;">
                    Peringatan: Ada <strong>{{ $tanpaProdusenCount }}</strong> data DSSD tanpa Produsen Data. Data tetap dihitung pada baris <strong>[Tanpa Produsen Data]</strong>. Silakan perbaiki file upload agar data terhitung ke produsen yang benar.
                </div>
                @endif

                <!-- Input Pencarian Data -->
                <div class="mb-4 d-flex justify-content-end">
                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari Perangkat Daerah…" style="padding-left: 2.5rem; border-radius: var(--radius);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="position-absolute text-muted" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="top: 50%; left: 12px; transform: translateY(-50%);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid var(--mist); border-radius: var(--radius);">
                    <table class="table app-table mb-0" id="laporanTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th>Perangkat Daerah/Produsen Data</th>
                                <th class="text-center">Jumlah Disepakati</th>
                                <th class="text-center">Data Kamasuta</th>
                                <th class="text-center" style="width: 12%">Data Spasial</th>
                                <th class="text-center" style="width: 10%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($produsenData as $index => $data)
                        <tr>
                            <td class="text-center text-muted fw-medium">{{ $index + 1 }}</td>
                            <td class="fw-medium">{{ $data->nama }}</td>
                            <td class="text-center table-code">
                                {{ $data->jumlah_disepakati }}
                                @if($data->_is_disepakati_auto)
                                    <span class="badge-status badge-status--info d-block mt-1" style="font-size: 0.65rem;">Otomatis</span>
                                @else
                                    <span class="badge-status badge-status--secondary d-block mt-1" style="font-size: 0.65rem;">Manual</span>
                                @endif
                            </td>
                            <td class="text-center table-code">
                                {{ $data->jumlah_kamasuta }}
                                <span class="badge-status badge-status--info d-block mt-1" style="font-size: 0.65rem;">Otomatis</span>
                            </td>
                            <td class="text-center table-code">
                                @if($tahunFilter)
                                <input type="number" min="0" value="{{ $data->jumlah_spasial }}"
                                    class="form-control form-control-sm text-center spasial-input"
                                    data-nama="{{ $data->nama }}"
                                    data-tahun="{{ $tahunFilter }}"
                                    style="width: 85px; margin: 0 auto; padding-right: 4px; padding-left: 4px;"
                                    data-original="{{ $data->jumlah_spasial }}">
                                @else
                                <span class="fw-medium">{{ $data->jumlah_spasial }}</span>
                                <span class="badge-status badge-status--secondary d-block mt-1" style="font-size: 0.65rem;">Manual</span>
                                @endif
                            </td>
                            <td class="text-center table-code fw-bold total-cell" data-kamasuta="{{ $data->jumlah_kamasuta }}" data-spasial="{{ $data->jumlah_spasial }}">
                                {{ $data->total }}
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Download Parameter -->
<div class="modal fade" id="downloadConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="downloadConfigForm" method="POST" action="">
            @csrf
            <input type="hidden" name="tahun" id="modalDownloadTahun" value="">
            <div class="modal-content">
                <header class="modal-header">
                    <h5 class="modal-title" id="downloadConfigModalTitle">Parameter Unduh Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </header>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size: 0.875rem;">
                        Silakan sesuaikan <strong>Keterangan SK</strong> dan <strong>Data Penandatangan</strong> di bawah ini sebelum mengunduh berkas laporan Word (.docx).
                    </div>
                    
                    <div class="mb-3" id="wrapperKeterangan">
                        <label class="form-label-custom">Keterangan (Kolom Keterangan)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan Keterangan SK...">Surat Keputusan Bupati Malang
Nomor 100.3.3.2/401/35.07.013/2024
tentang Data Statistik Sektoral Daerah
Kabupaten Malang Tahun 2025</textarea>
                        <div class="form-text">Teks ini akan dimasukkan pada kolom Keterangan di berkas rekapitulasi.</div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Judul Laporan & Data Penandatangan</h6>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Tahun di Judul Laporan (Kop)</label>
                            <input type="text" name="tahun_judul" class="form-control" value="2025" required>
                            <div class="form-text">Tahun ini akan ditampilkan di judul utama tabel pada laporan.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Kota & Tanggal Penandatanganan</label>
                            <input type="text" name="tanggal_ttd" class="form-control" value="Malang, Februari 2026" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Jabatan Penandatangan</label>
                            <input type="text" name="jabatan" class="form-control" value="KEPALA DINAS KOMUNIKASI DAN INFORMATIKA" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Penandatangan</label>
                            <input type="text" name="nama_ttd" class="form-control" value="Drs. ATSALIS SUPRIYANTO, M.Si" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Pangkat / Golongan</label>
                            <input type="text" name="pangkat_ttd" class="form-control" value="Pembina Utama Muda" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">NIP Penandatangan</label>
                            <input type="text" name="nip_ttd" class="form-control" value="196711301988091001" required>
                        </div>
                    </div>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">
                        <svg xmlns="http://www.w3.org/2000/svg" class="me-1" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Berkas (.docx)
                    </button>
                </footer>
            </div>
        </form>
    </div>
</div>

<script>
    function openDownloadModal(url, title, showKeterangan = true) {
        // Hilangkan class is-running dari preloader bar jika ada
        var bar = document.getElementById('nav-progress');
        if(bar) {
            bar.classList.remove('is-running');
            bar.classList.add('is-done');
        }

        const form = document.getElementById('downloadConfigForm');
        form.action = url;

        document.getElementById('downloadConfigModalTitle').innerText = 'Parameter Unduh: ' + title;

        // Ambil tahun dari filter
        const filterTahun = document.getElementById('filter-tahun');
        document.getElementById('modalDownloadTahun').value = (filterTahun && filterTahun.value) ? filterTahun.value : '';

        // Tampilkan/Sembunyikan wrapperKeterangan sesuai jenis laporan
        const wrapperKet = document.getElementById('wrapperKeterangan');
        if(wrapperKet) {
            wrapperKet.style.display = showKeterangan ? 'block' : 'none';
        }

        // Tampilkan Modal Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('downloadConfigModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.spasial-input');
        inputs.forEach(function(input) {
            input.addEventListener('change', function() {
                const nama = this.dataset.nama;
                const tahun = this.dataset.tahun;
                const val = parseInt(this.value) || 0;
                const original = parseInt(this.dataset.original) || 0;
                const row = this.closest('tr');
                const totalCell = row.querySelector('.total-cell');

                fetch('{{ route("laporan.spasial.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        nama: nama,
                        tahun: tahun,
                        jumlah_spasial: val,
                    }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        const kamasuta = parseInt(totalCell.dataset.kamasuta) || 0;
                        totalCell.textContent = kamasuta + val;
                        totalCell.dataset.spasial = val;
                        document.getElementById('totalSpasial').textContent = data.totalSpasial;
                        document.getElementById('totalKamasuta').textContent = (function() {
                            const allTotals = document.querySelectorAll('.total-cell');
                            let sumKamasuta = 0;
                            allTotals.forEach(function(c) { sumKamasuta += parseInt(c.dataset.kamasuta) || 0; });
                            return sumKamasuta;
                        })();
                        const totalEl = document.querySelector('#persentaseCapaian');
                        totalEl.textContent = data.persentase + '%';
                    }
                })
                .catch(function() {
                    alert('Gagal menyimpan data spasial. Coba lagi.');
                    input.value = original;
                });
            });
        });
    });
</script>
<!-- Modal Tambah Data Spasial Manual -->
<div class="modal fade" id="tambahSpasialModal" tabindex="-1" aria-labelledby="tambahSpasialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('laporan.spasial.manual') }}">
            @csrf
            <header class="modal-header">
                <h5 class="modal-title" id="tambahSpasialModalLabel">Tambah Data Spasial Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </header>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="spasial-nama" class="form-label-custom">Nama Perangkat Daerah / Produsen Data <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="spasial-nama" class="form-control" list="instansiDatalist" placeholder="Ketik nama produsen data..." required autocomplete="off">
                    <datalist id="instansiDatalist">
                        @foreach($instansiOptions as $option)
                            <option value="{{ $option }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="mb-3">
                    <label for="spasial-tahun" class="form-label-custom">Tahun Data <span class="text-danger">*</span></label>
                    <select name="tahun" id="spasial-tahun" class="form-select" required>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ ($tahunFilter ?: date('Y')) == $year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="spasial-jumlah" class="form-label-custom">Jumlah Data Spasial <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_spasial" id="spasial-jumlah" class="form-control" min="0" value="0" required>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn-primary-custom">Simpan Data Spasial</button>
            </footer>
        </form>
    </div>
</div>

@endsection
