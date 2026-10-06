<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Stok;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Menambah stok barang.
     */
    public function masuk(
        int $barangId,
        float $jumlah,
        ?string $referensi = null,
        ?string $keterangan = null
    ): Stok {
        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah stok masuk harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $barangId,
            $jumlah,
            $referensi,
            $keterangan
        ) {
            // Pastikan barang tersedia
            Barang::findOrFail($barangId);

            // Buat stok jika belum ada
            $stok = Stok::firstOrCreate(
                ['barang_id' => $barangId],
                ['jumlah' => 0]
            );

            // Kunci baris stok agar aman dari perubahan bersamaan
            $stok = Stok::where('id', $stok->id)
                ->lockForUpdate()
                ->first();

            $stokSebelum = (float) $stok->jumlah;
            $stokSesudah = $stokSebelum + $jumlah;

            // Update stok
            $stok->update([
                'jumlah' => $stokSesudah,
            ]);

            // Catat riwayat mutasi
            StokMutasi::create([
                'barang_id' => $barangId,
                'tipe' => 'masuk',
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi' => $referensi,
                'keterangan' => $keterangan,
            ]);

            return $stok->fresh();
        });
    }

    /**
     * Mengurangi stok barang.
     */
    public function keluar(
        int $barangId,
        float $jumlah,
        ?string $referensi = null,
        ?string $keterangan = null
    ): Stok {
        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah stok keluar harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $barangId,
            $jumlah,
            $referensi,
            $keterangan
        ) {
            // Pastikan barang tersedia
            Barang::findOrFail($barangId);

            // Ambil stok dan kunci barisnya
            $stok = Stok::where('barang_id', $barangId)
                ->lockForUpdate()
                ->first();

            // Jika stok belum tersedia
            if (!$stok) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok barang belum tersedia.',
                ]);
            }

            $stokSebelum = (float) $stok->jumlah;

            // Jangan sampai stok menjadi minus
            if ($jumlah > $stokSebelum) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok tidak mencukupi. Stok tersedia: {$stokSebelum}.",
                ]);
            }

            $stokSesudah = $stokSebelum - $jumlah;

            // Update stok
            $stok->update([
                'jumlah' => $stokSesudah,
            ]);

            // Catat riwayat mutasi
            StokMutasi::create([
                'barang_id' => $barangId,
                'tipe' => 'keluar',
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi' => $referensi,
                'keterangan' => $keterangan,
            ]);

            return $stok->fresh();
        });
    }

    /**
     * Menyesuaikan stok menjadi jumlah tertentu.
     */
    public function penyesuaian(
        int $barangId,
        float $stokBaru,
        ?string $referensi = null,
        ?string $keterangan = null
    ): Stok {
        if ($stokBaru < 0) {
            throw ValidationException::withMessages([
                'stok' => 'Stok tidak boleh kurang dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $barangId,
            $stokBaru,
            $referensi,
            $keterangan
        ) {
            // Pastikan barang tersedia
            Barang::findOrFail($barangId);

            // Buat stok jika belum ada
            $stok = Stok::firstOrCreate(
                ['barang_id' => $barangId],
                ['jumlah' => 0]
            );

            // Kunci baris stok 
            $stok = Stok::where('id', $stok->id)
                ->lockForUpdate()
                ->first();

            $stokSebelum = (float) $stok->jumlah;
            $stokSesudah = $stokBaru;

            // Hitung selisih
            $selisih = abs($stokSesudah - $stokSebelum);

            // Update stok
            $stok->update([
                'jumlah' => $stokSesudah,
            ]);

            // Catat mutasi
            StokMutasi::create([
                'barang_id' => $barangId,
                'tipe' => 'penyesuaian',
                'jumlah' => $selisih,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi' => $referensi,
                'keterangan' => $keterangan,
            ]);

            return $stok->fresh();
        });
    }
}