<!-- Create Manual Modal -->
<div class="modal fade" id="createDataModal" tabindex="-1" aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('imported-dssd-data.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createDataModalLabel">Tambah Data DSSD Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @php $item = null; @endphp
                        @include('partials.imported-dssd-form', ['item' => null])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
