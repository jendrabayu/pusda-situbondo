<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelRpjmd extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tabel_rpjmd';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'skpd_id', 'parent_id', 'menu_name'];


    public function parent()

    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(self::class, 'parent_id');
    }


    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function fiturRpjmd()
    {
        return $this->hasMany(FiturRpjmd::class, 'tabel_rpjmd_id');
    }

    public function uraianRpmd()
    {
        return $this->hasMany(UraianRpjmd::class, 'tabel_rpjmd_id');
    }

    public function fileRpjmd()
    {
        return $this->hasMany(FileRpjmd::class, 'tabel_rpjmd_id');
    }
}
