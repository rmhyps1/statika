<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportDssdFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'array'],
            'file.*' => ['file', 'mimes:csv,txt,xlsx,xls', 'max:40960', 'extensions:csv,txt,xlsx,xls'],
            'tahun' => ['required', 'integer', 'min:1900', 'max:2100'],
            'jenis_data' => ['nullable', 'in:OPD,Kecamatan,Kelurahan'],
        ];
    }
}
