<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('dssd.compare-kamasuta') }}" method="POST" onsubmit="showSyncLoading(this)">
            @csrf
            <div class="modal-content" id="syncModalContentHome">
                <header class="modal-header">
                    <h2 class="modal-title" id="syncModalLabel">Compare Data Kamasuta</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </header>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Pilih tahun data indikator Kamasuta yang ingin Anda compare (cocokkan) dengan data CSV/Excel yang telah Anda upload. Status ketersediaan data akan diubah secara otomatis jika ditemukan kecocokan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Jenis Data</label>
                        <select name="jenis_data" class="form-select">
                            <option value="">Semua</option>
                            <option value="OPD">OPD</option>
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kelurahan">Kelurahan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Tahun Compare</label>
                        <select name="tahun" class="form-select" required>
                            <option value="">-- Pilih Tahun --</option>
                            @php
                                $currentYear = date('Y');
                            @endphp
                            @for($i = $currentYear - 2; $i <= $currentYear + 2; $i++)
                                <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">Mulai Compare</button>
                </footer>
            </div>
            
            <!-- Loading State -->
            <div class="modal-content d-none" id="syncModalLoadingHome">
                <div class="modal-body text-center py-5">
                    <div class="export-spinner mb-4"></div>
                    <h3 class="app-card-title mb-2">Membandingkan Data...</h3>
                    <p class="text-muted mb-0">Sedang menarik data indikator dari API Kamasuta dan mencocokkannya dengan inventaris DSSD. Proses ini mungkin memakan waktu beberapa saat.</p>
                </div>
            </div>
        </form>
    </div>
</div>
