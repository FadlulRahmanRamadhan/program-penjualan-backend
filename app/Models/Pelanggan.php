<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'no_hp',
        'alamat',
        'limit_piutang',
        'status',
    ];

    protected $casts = [
        'limit_piutang' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class);
    }
}