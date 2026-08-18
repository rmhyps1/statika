<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('imported-dssd-data.destroy-all') }}" method="POST" class="modal-content" onsubmit="showDeleteAllLoading(this)">
            @csrf
            @method('DELETE')
            <div id="deleteAllModalContent">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllModalLabel">Hapus Data DSSD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" style="width: 1.5rem; height: 1.5rem;">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                        </svg>
                        Pilih data mana yang akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan.
                    </div>

                    <div class="mb-3">
                        <label for="delete-jenis-data" class="form-label form-label-custom">Jenis Data</label>
                        <select name="jenis_data" id="delete-jenis-data" class="form-select">
                            <option value="">Semua Jenis (OPD, Kecamatan, Kelurahan)</option>
                            <option value="OPD">OPD</option>
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kelurahan">Kelurahan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="delete-tahun" class="form-label form-label-custom">Tahun Data</label>
                        <select name="tahun" id="delete-tahun" class="form-select">
                            <option value="">Semua Tahun</option>
                            @php $currentYear = date('Y'); @endphp
                            @for($i = $currentYear - 3; $i <= $currentYear + 2; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Data</button>
                </div>
            </div>
            
            <!-- Loading State Hapus Semua -->
            <div class="modal-content d-none" id="deleteAllModalLoading">
                <div class="modal-body text-center py-5">
                    <div class="export-spinner mb-4"></div>
                    <h3 class="app-card-title mb-2">Menghapus Data...</h3>
                    <p class="text-muted mb-0">Sedang menghapus data terpilih dan membersihkan tabel cermin. Mohon tidak menutup halaman ini.</p>
                </div>
            </div>
        </form>
    </div>
</div>