<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranPenjualan extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_penjualan';

    protected $fillable = [
        'penjualan_id',
        'tanggal_bayar',
        'jumlah',
        'metode',
        'referensi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }
}