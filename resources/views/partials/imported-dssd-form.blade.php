<div class="col-md-3 form-field">
    <label for="kode-dssd-{{ $item?->id ?? 'new' }}" class="form-label-custom">Kode DSSD <span class="text-danger" aria-hidden="true">*</span></label>
    <input type="text" id="kode-dssd-{{ $item?->id ?? 'new' }}" name="kode_dssd" value="{{ old('kode_dssd', $item?->kode_dssd) }}" class="form-control" required aria-required="true" autocomplete="off">
</div>
<div class="col-md-9 form-field">
    <label for="uraian-dssd-{{ $item?->id ?? 'new' }}" class="form-label-custom">Uraian DSSD <span class="text-danger" aria-hidden="true">*</span></label>
    <input type="text" id="uraian-dssd-{{ $item?->id ?? 'new' }}" name="uraian_dssd" value="{{ old('uraian_dssd', $item?->uraian_dssd) }}" class="form-control" required aria-required="true" autocomplete="off">
</div>
<div class="col-md-4 form-field">
    <label for="produsen-data-{{ $item?->id ?? 'new' }}" class="form-label-custom">Produsen Data</label>
    <input type="text" id="produsen-data-{{ $item?->id ?? 'new' }}" name="produsen_data" value="{{ old('produsen_data', $item?->produsen_data) }}" class="form-control" autocomplete="off">
</div>
<div class="col-md-2 form-field">
    <label for="ketersediaan-data-{{ $item?->id ?? 'new' }}" class="form-label-custom">Ketersediaan <span class="text-danger" aria-hidden="true">*</span></label>
    <select id="ketersediaan-data-{{ $item?->id ?? 'new' }}" name="ketersediaan_data" class="form-select" required aria-required="true">
        <option value="ada" @selected(old('ketersediaan_data', $item?->ketersediaan_data) === 'ada')>Ada</option>
        <option value="tidak" @selected(old('ketersediaan_data', $item?->ketersediaan_data) === 'tidak')>Tidak</option>
    </select>
</div>
<div class="col-md-3 form-field">
    <label for="jenis-data-{{ $item?->id ?? 'new' }}" class="form-label-custom">Jenis Data</label>
    <select id="jenis-data-{{ $item?->id ?? 'new' }}" name="jenis_data" class="form-select" required aria-required="true">
        <option value="OPD" @selected(old('jenis_data', $item?->jenis_data ?? 'OPD') === 'OPD')>OPD</option>
        <option value="Kecamatan" @selected(old('jenis_data', $item?->jenis_data) === 'Kecamatan')>Kecamatan</option>
        <option value="Kelurahan" @selected(old('jenis_data', $item?->jenis_data) === 'Kelurahan')>Kelurahan</option>
    </select>
</div>
<div class="col-md-3 form-field">
    <label for="jenis-produsen-{{ $item?->id ?? 'new' }}" class="form-label-custom">Jenis Produsen</label>
    <input type="text" id="jenis-produsen-{{ $item?->id ?? 'new' }}" name="jenis_produsen" value="{{ old('jenis_produsen', $item?->jenis_produsen) }}" class="form-control" autocomplete="off">
</div>
<div class="col-md-2 form-field">
    <label for="tahun-{{ $item?->id ?? 'new' }}" class="form-label-custom">Tahun</label>
    <select name="tahun" id="tahun-{{ $item?->id ?? 'new' }}" class="form-select">
        @php $currentYear = date('Y'); @endphp
        @for($i = $currentYear - 3; $i <= $currentYear + 1; $i++)
            <option value="{{ $i }}" {{ old('tahun', $item?->tahun) == $i ? 'selected' : '' }}>{{ $i }}</option>
        @endfor
    </select>
</div>
<div class="col-md-4 form-field">
    <label for="satuan-{{ $item?->id ?? 'new' }}" class="form-label-custom">Satuan</label>
    <input type="text" id="satuan-{{ $item?->id ?? 'new' }}" name="satuan" value="{{ old('satuan', $item?->satuan) }}" class="form-control" autocomplete="off">
</div>
<div class="col-md-4 form-field">
    <label for="tag-urusan-{{ $item?->id ?? 'new' }}" class="form-label-custom">Tag Urusan</label>
    <input type="text" id="tag-urusan-{{ $item?->id ?? 'new' }}" name="tag_urusan" value="{{ old('tag_urusan', $item?->tag_urusan) }}" class="form-control" autocomplete="off">
</div>
<div class="col-md-4 form-field">
    <label for="info-sub-kegiatan-{{ $item?->id ?? 'new' }}" class="form-label-custom">Info Sub Kegiatan</label>
    <input type="text" id="info-sub-kegiatan-{{ $item?->id ?? 'new' }}" name="info_sub_kegiatan" value="{{ old('info_sub_kegiatan', $item?->info_sub_kegiatan) }}" class="form-control" autocomplete="off">
</div>
<div class="col-md-6 form-field">
    <label for="definisi-operasional-{{ $item?->id ?? 'new' }}" class="form-label-custom">Definisi Operasional</label>
    <textarea id="definisi-operasional-{{ $item?->id ?? 'new' }}" name="definisi_operasional" class="form-control" rows="2">{{ old('definisi_operasional', $item?->definisi_operasional) }}</textarea>
</div>
<div class="col-md-6 form-field">
    <label for="keterangan-{{ $item?->id ?? 'new' }}" class="form-label-custom">Keterangan</label>
    <textarea id="keterangan-{{ $item?->id ?? 'new' }}" name="keterangan" class="form-control" rows="2">{{ old('keterangan', $item?->keterangan) }}</textarea>
</div>