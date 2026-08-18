<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportedDssdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_dssd' => ['required', 'string', 'max:100'],
            'uraian_dssd' => ['required', 'string'],
            'produsen_data' => ['nullable', 'string', 'max:255'],
            'ketersediaan_data' => ['required', 'in:ada,tidak'],
            'jenis_data' => ['required', 'in:OPD,Kecamatan,Kelurahan'],
            'jenis_produsen' => ['nullable', 'string', 'max:255'],
            'tahun' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'satuan' => ['nullable', 'string', 'max:255'],
            'definisi_operasional' => ['nullable', 'string'],
            'tag_urusan' => ['nullable', 'string', 'max:255'],
            'info_sub_kegiatan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
