<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSpasialManual extends Model
{
    use HasFactory;

    protected $table = 'data_spasial_manual';

    protected $fillable = [
        'nama_produsen',
        'tahun',
        'jumlah_spasial',
    ];

    protected $casts = [
        'jumlah_spasial' => 'integer',
    ];
}
