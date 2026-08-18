<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DssdOpd extends Model
{
    use HasFactory;

    protected $table = 'dssd_opd';

    protected $fillable = [
        'kode_dssd',
        'uraian_dssd',
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
}
