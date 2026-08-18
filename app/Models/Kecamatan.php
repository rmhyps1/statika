<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $fillable = [
        'kode_kecamatan',
        'nama_kecamatan',
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

    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class);
    }
}
