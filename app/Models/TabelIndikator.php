<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelIndikator extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tabel_indikator';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'parent_id', 'menu_name'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function uraianIndikator()
    {
        return $this->hasMany(UraianIndikator::class, 'tabel_indikator_id');
    }

    public function fiturIndikator()
    {
        return $this->hasMany(FiturIndikator::class, 'tabel_indikator_id');
    }

    public function fileIndikator()
    {
        return $this->hasMany(FileIndikator::class, 'tabel_indikator_id');
    }
}