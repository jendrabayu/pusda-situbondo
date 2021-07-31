<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IsiIndikator extends Model
{
    use HasFactory;

    protected $table = 'isi_indikator';

    protected $fillable = [
        'uraian_indikator_id',
        'tahun',
        'isi'
    ];

    public function uraianIndikator()
    {
        return $this->belongsTo(UraianIndikator::class, 'uraian_indikator_id');
    }

    public static function getYears()
    {
        $years = self::orderByDesc('tahun')
            ->groupBy('tahun')
            ->get('tahun')
            ->sortBy('tahun');

        $years = $years->map(fn ($item) => $item->tahun)->values();
        return $years;
    }
}
