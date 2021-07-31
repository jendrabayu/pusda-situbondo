<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IsiBps extends Model
{
    use HasFactory;

    protected $table = 'isi_bps';

    protected $fillable = [
        'uraian_bps_id',
        'tahun',
        'isi'
    ];

    public function uraianBps()
    {
        return $this->belongsTo(UraianBps::class, 'uraian_bps_id');
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
