<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <header class="modal-header">
                <h2 class="modal-title" id="exportModalLabel">Pilih Jenis Ekspor</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup pilihan ekspor"></button>
            </header>
            <div class="modal-body">
                <div class="d-grid gap-3">
                    <form action="{{ route('imported-dssd-data.export') }}" method="GET" class="d-grid" data-export-loading-form>
                        @foreach(request()->query() as $key => $value)
                            @if($key !== 'import_page')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <button type="submit" class="export-choice" data-export-loading="Menyiapkan file ekspor…">
                            Ekspor Data Saat Ini
                            <span class="export-choice-desc">Mengunduh data DSSD berdasarkan filter yang sedang aktif.</span>
                        </button>
                    </form>
                    <a href="{{ route('dssd.template') }}" class="export-choice" data-export-loading="Menyiapkan template DSSD…">
                        Unduh Template DSSD
                        <span class="export-choice-desc">Mengunduh format Excel kosong untuk pengisian data DSSD.</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
