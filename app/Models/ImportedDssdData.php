<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedDssdData extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_dssd',
        'uraian_dssd',
        'produsen_data',
        'ketersediaan_data',
        'ketersediaan_source',
        'satuan',
        'definisi_operasional',
        'tag_urusan',
        'info_sub_kegiatan',
        'keterangan',
        'matched_kamasuta_code',
        'matched_kamasuta_title',
        'matched_by',
        'last_compared_at',
        'jenis_data',
        'jenis_produsen',
        'tahun',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'last_compared_at' => 'datetime',
    ];

    public function getStatusAttribute(): string
    {
        if ($this->ketersediaan_source === 'manual') {
            return 'manual';
        }

        if (! empty($this->matched_kamasuta_code) || ! empty($this->matched_kamasuta_title)) {
            return 'matched';
        }

        if (! empty($this->last_compared_at)) {
            return 'unmatched';
        }

        return 'not_synced';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'manual' => 'Manual',
            'matched' => 'Otomatis',
            'unmatched' => 'Tidak ditemukan',
            'not_synced' => 'Belum sinkron',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'manual', 'not_synced' => 'badge-status--secondary',
            'matched' => 'badge-status--info',
            'unmatched' => 'badge-status--warning',
        };
    }

    public function getKategoriLabelAttribute(): string
    {
        if (str_starts_with((string) $this->kode_dssd, '35.07')) {
            return 'Sektoral';
        }

        if (str_starts_with((string) $this->kode_dssd, 'DG.')) {
            return 'Spasial';
        }

        return 'e-Walidata';
    }

    public function getKategoriBadgeClassAttribute(): string
    {
        return match ($this->kategori_label) {
            'Sektoral' => 'badge-status--info',
            'Spasial' => 'badge-status--purple',
            'e-Walidata' => 'badge-status--warning',
        };
    }

    public function scopeStatus($query, string $status)
    {
        return match ($status) {
            'manual' => $query->where('ketersediaan_source', 'manual'),

            'matched' => $query->where('ketersediaan_source', '!=', 'manual')
                ->where(function ($q) {
                    $q->whereNotNull('matched_kamasuta_code')
                        ->orWhereNotNull('matched_kamasuta_title');
                }),

            'unmatched' => $query->where('ketersediaan_source', '!=', 'manual')
                ->whereNull('matched_kamasuta_code')
                ->whereNull('matched_kamasuta_title')
                ->whereNotNull('last_compared_at'),

            'not_synced' => $query->where('ketersediaan_source', '!=', 'manual')
                ->whereNull('matched_kamasuta_code')
                ->whereNull('matched_kamasuta_title')
                ->whereNull('last_compared_at'),

            default => $query,
        };
    }

    public function scopeKategori($query, string $kategori)
    {
        return match ($kategori) {
            'Sektoral' => $query->where('kode_dssd', 'LIKE', '35.07%'),
            'Spasial' => $query->where('kode_dssd', 'LIKE', 'DG.%'),
            'e-Walidata' => $query->where('kode_dssd', 'NOT LIKE', '35.07%')
                ->where('kode_dssd', 'NOT LIKE', 'DG.%'),
            default => $query,
        };
    }

    public function scopeFilter($query, array $criteria)
    {
        if (! empty($criteria['jenis_data'])) {
            $query->where('jenis_data', $criteria['jenis_data']);
        }

        if (! empty($criteria['tahun'])) {
            $query->where('tahun', $criteria['tahun']);
        }

        if (! empty($criteria['produsen_data'])) {
            $query->where('produsen_data', $criteria['produsen_data']);
        }

        if (! empty($criteria['kategori_data'])) {
            $query->kategori($criteria['kategori_data']);
        }

        if (! empty($criteria['kamasuta_status'])) {
            $query->status($criteria['kamasuta_status']);
        }

        if (! empty($criteria['search'])) {
            $keywords = explode(' ', $criteria['search']);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    if ($keyword !== '') {
                        $q->where('uraian_dssd', 'LIKE', '%'.$keyword.'%');
                    }
                }
            });
        }

        return $query;
    }
}
