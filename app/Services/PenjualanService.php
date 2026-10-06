<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\PembayaranPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjualanService
{
    public function buatPenjualan(array $data): Penjualan
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | 1. Validasi pelanggan
            |--------------------------------------------------------------------------
            */

            $pelanggan = null;

            if (!empty($data['pelanggan_id'])) {
                $pelanggan = Pelanggan::findOrFail(
                    $data['pelanggan_id']
                );

                if (!$pelanggan->status) {
                    throw ValidationException::withMessages([
                        'pelanggan_id' =>
                            'Pelanggan sedang tidak aktif.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Buat header penjualan
            |--------------------------------------------------------------------------
            */

            $penjualan = Penjualan::create([
                'no_transaksi' => 'TEMP-' . uniqid(),
                'tanggal' => $data['tanggal'] ?? now(),
                'pelanggan_id' => $data['pelanggan_id'] ?? null,

                'subtotal' => 0,
                'diskon' => $data['diskon'] ?? 0,
                'pajak' => $data['pajak'] ?? 0,
                'biaya_lain' => $data['biaya_lain'] ?? 0,

                'total' => 0,
                'dibayar' => 0,
                'piutang' => 0,

                'jatuh_tempo' => $data['jatuh_tempo'] ?? null,

                'status' => 'draft',

                'keterangan' =>
                    $data['keterangan'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 3. Buat nomor invoice
            |--------------------------------------------------------------------------
            */

            $noTransaksi =
                'INV-' .
                now()->format('Ymd') .
                '-' .
                str_pad(
                    $penjualan->id,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

            $penjualan->update([
                'no_transaksi' => $noTransaksi,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Proses detail barang
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach ($data['detail'] as $item) {

                $barang = Barang::findOrFail(
                    $item['barang_id']
                );

                if (!$barang->status) {
                    throw ValidationException::withMessages([
                        'detail' =>
                            "Barang {$barang->nama_barang} sedang tidak aktif.",
                    ]);
                }

                $qty = (float) $item['qty'];

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'detail' =>
                            'Qty barang harus lebih dari 0.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Harga
                |--------------------------------------------------------------------------
                */

                $harga = isset($item['harga'])
                    ? (float) $item['harga']
                    : (float) $barang->harga_jual;

                if ($harga < 0) {
                    throw ValidationException::withMessages([
                        'detail' =>
                            'Harga barang tidak boleh negatif.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Diskon item
                |--------------------------------------------------------------------------
                */

                $diskon = isset($item['diskon'])
                    ? (float) $item['diskon']
                    : 0;

                if ($diskon < 0) {
                    throw ValidationException::withMessages([
                        'detail' =>
                            'Diskon barang tidak boleh negatif.',
                    ]);
                }

                $nilaiKotor = $qty * $harga;

                $nilaiDiskon = min(
                    $diskon,
                    $nilaiKotor
                );

                $nilaiSubtotal =
                    $nilaiKotor - $nilaiDiskon;

                /*
                |--------------------------------------------------------------------------
                | Simpan detail
                |--------------------------------------------------------------------------
                */

                $penjualan->detail()->create([
                    'barang_id' => $barang->id,
                    'qty' => $qty,
                    'harga' => $harga,
                    'diskon' => $nilaiDiskon,
                    'subtotal' => $nilaiSubtotal,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Kurangi stok
                |--------------------------------------------------------------------------
                */

                app(InventoryService::class)->keluar(
                    barangId: $barang->id,
                    jumlah: $qty,
                    referensi: $noTransaksi,
                    keterangan: 'Barang keluar untuk penjualan'
                );

                $subtotal += $nilaiSubtotal;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Hitung total
            |--------------------------------------------------------------------------
            */

            $diskonHeader =
                (float) ($data['diskon'] ?? 0);

            $pajak =
                (float) ($data['pajak'] ?? 0);

            $biayaLain =
                (float) ($data['biaya_lain'] ?? 0);

            $total =
                $subtotal
                - $diskonHeader
                + $pajak
                + $biayaLain;

            if ($total < 0) {
                $total = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Pembayaran awal
            |--------------------------------------------------------------------------
            */

            $dibayar =
                isset($data['pembayaran']['jumlah'])
                    ? (float) $data['pembayaran']['jumlah']
                    : 0;

            if ($dibayar < 0) {
                throw ValidationException::withMessages([
                    'pembayaran' =>
                        'Jumlah pembayaran tidak boleh negatif.',
                ]);
            }

           

           $piutang = max(0, $total - $dibayar);

            /*
            |--------------------------------------------------------------------------
            | 7. Validasi limit piutang
            |--------------------------------------------------------------------------
            */

            if ($piutang > 0) {

                if (!$pelanggan) {
                    throw ValidationException::withMessages([
                        'pelanggan_id' =>
                            'Pelanggan wajib dipilih jika transaksi memiliki piutang.',
                    ]);
                }

                if (
                    $piutang >
                    (float) $pelanggan->limit_piutang
                ) {
                    throw ValidationException::withMessages([
                        'pembayaran' =>
                            'Piutang melebihi limit piutang pelanggan.',
                    ]);
                }

                if (empty($data['jatuh_tempo'])) {
                    throw ValidationException::withMessages([
                        'jatuh_tempo' =>
                            'Tanggal jatuh tempo wajib diisi jika terdapat piutang.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Simpan total transaksi
            |--------------------------------------------------------------------------
            */

            $penjualan->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'dibayar' => $dibayar,
                'piutang' => $piutang,
                'status' => 'selesai',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 9. Simpan pembayaran
            |--------------------------------------------------------------------------
            */

            if ($dibayar > 0) {

                PembayaranPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'tanggal_bayar' => now(),
                    'jumlah' => $dibayar,
                    'metode' =>
                        $data['pembayaran']['metode']
                        ?? 'tunai',
                    'referensi' =>
                        $data['pembayaran']['referensi']
                        ?? null,
                    'keterangan' =>
                        'Pembayaran awal transaksi',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 10. Kembalikan transaksi lengkap
            |--------------------------------------------------------------------------
            */

            return $penjualan->load([
                'pelanggan',
                'detail.barang',
                'pembayaran',
            ]);
        });
    }
}