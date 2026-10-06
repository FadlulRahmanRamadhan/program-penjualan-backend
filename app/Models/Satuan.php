<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuan';

    protected $fillable = [
        'nama_satuan',
        'simbol',
        'keterangan',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }
}