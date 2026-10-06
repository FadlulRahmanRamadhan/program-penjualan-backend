<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merk extends Model
{
    use HasFactory;

    protected $table = 'merk';

    protected $fillable = [
        'nama_merk',
        'keterangan',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }
}