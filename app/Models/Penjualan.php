<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'pelanggan_id',
        'subtotal',
        'diskon',
        'pajak',
        'biaya_lain',
        'total',
        'dibayar',
        'piutang',
        'jatuh_tempo',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'pajak' => 'decimal:2',
        'biaya_lain' => 'decimal:2',
        'total' => 'decimal:2',
        'dibayar' => 'decimal:2',
        'piutang' => 'decimal:2',
        'jatuh_tempo' => 'date',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class);
    }
    
    public function pembayaran(): HasMany
{
    return $this->hasMany(PembayaranPenjualan::class);
}
}