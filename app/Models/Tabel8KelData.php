<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabel8KelData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tabel_8keldata';

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

    public function uraian8KelData()
    {
        return $this->hasMany(Uraian8KelData::class, 'tabel_8keldata_id');
    }

    public function fitur8KelData()
    {
        return $this->hasMany(Fitur8KelData::class, 'tabel_8keldata_id');
    }

    public function file8KelData()
    {
        return $this->hasMany(File8KelData::class, 'tabel_8keldata_id');
    }
}