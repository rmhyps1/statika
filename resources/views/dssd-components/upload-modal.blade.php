    <!-- Modal Upload Data -->
    <div class="modal fade" id="uploadDataModal" tabindex="-1" aria-labelledby="uploadDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('imported-dssd-data.import') }}" method="POST" enctype="multipart/form-data" onsubmit="showUploadLoading(this)">
                    @csrf
                    <div id="uploadModalContent">
                        <header class="modal-header">
                            <h2 class="modal-title" id="uploadDataModalLabel">Upload Data DSSD</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </header>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="import-tahun" class="form-label form-label-custom">Tahun Data</label>
                                <select name="tahun" id="import-tahun" class="form-select" required>
                                    @php $currentYear = date('Y'); @endphp
                                    @for($i = $currentYear - 2; $i <= $currentYear + 2; $i++)
                                        <option value="{{ $i }}" @selected($i == $currentYear)>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="import-file" class="form-label form-label-custom">Pilih File (CSV/XLSX)</label>
                                <div class="input-group">
                                    <input type="file" id="import-file-dummy" class="form-control" accept=".csv,.txt,.xlsx,.xls" multiple onchange="handleFileSelect(this)">
                                </div>
                                <div class="form-text">Bisa pilih lebih dari satu file. Pilih lagi untuk menambahkan file.</div>
                                
                                <div id="uploadProgressContainer" class="mt-3 d-none">
                                    <!-- Container untuk nampung input file beneran (hidden) -->
                                    <div id="hiddenFileInputs"></div>
                                    
                                    <ul id="selectedFileList" class="list-unstyled mb-0 text-muted"></ul>
                                </div>
                            </div>
                        </div>
                        <footer class="modal-footer">
                            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-primary-custom" id="btnSubmitUpload" disabled>Simpan Data</button>
                        </footer>
                    </div>
                    
                    <!-- Loading State Upload -->
                    <div class="modal-content d-none" id="uploadModalLoading">
                        <div class="modal-body text-center py-5">
                            <div class="export-spinner mb-4"></div>
                            <h3 class="app-card-title mb-2">Menyimpan ke Database...</h3>
                            <p class="text-muted mb-0">File sedang diproses server dan dimasukkan ke database. Memakan waktu sesuai jumlah baris data.</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
