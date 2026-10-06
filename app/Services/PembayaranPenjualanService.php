<?php

namespace App\Services;

use App\Models\PembayaranPenjualan;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranPenjualanService
{
    public function bayar(
        int $penjualanId,
        float $jumlah,
        string $metode = 'tunai',
        ?string $referensi = null,
        ?string $keterangan = null
    ): Penjualan {
        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah pembayaran harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $penjualanId,
            $jumlah,
            $metode,
            $referensi,
            $keterangan
        ) {
            $penjualan = Penjualan::where('id', $penjualanId)
                ->lockForUpdate()
                ->firstOrFail();

            $piutangSaatIni = (float) $penjualan->piutang;

            if ($piutangSaatIni <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Transaksi ini sudah lunas.',
                ]);
            }

            if ($jumlah > $piutangSaatIni) {
                throw ValidationException::withMessages([
                    'jumlah' =>
                        "Pembayaran melebihi piutang. Sisa piutang: Rp " .
                        number_format(
                            $piutangSaatIni,
                            0,
                            ',',
                            '.'
                        ),
                ]);
            }

            $dibayarBaru =
                (float) $penjualan->dibayar + $jumlah;

            $piutangBaru =
                $piutangSaatIni - $jumlah;

            PembayaranPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'tanggal_bayar' => now(),
                'jumlah' => $jumlah,
                'metode' => $metode,
                'referensi' => $referensi,
                'keterangan' =>
                    $keterangan ?? 'Pembayaran piutang',
            ]);

            $penjualan->update([
                'dibayar' => $dibayarBaru,
                'piutang' => $piutangBaru,
            ]);

            return $penjualan->fresh()->load([
                'pelanggan',
                'detail.barang',
                'pembayaran',
            ]);
        });
    }
}