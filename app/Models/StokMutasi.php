<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMutasi extends Model
{
    use HasFactory;

    protected $table = 'stok_mutasi';

    protected $fillable = [
        'barang_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'referensi',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'stok_sebelum' => 'decimal:2',
        'stok_sesudah' => 'decimal:2',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}