<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelurahan extends Model
{
    use HasFactory;

    protected $table = 'kelurahan';

    protected $fillable = [
        'kecamatan_id',
        'kode_kelurahan',
        'nama_kelurahan',
        'produsen_data',
        'jenis_data',
        'jenis_produsen',
        'tahun',
        'satuan',
        'definisi_operasional',
        'tag_urusan',
        'info_sub_kegiatan',
        'keterangan',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
