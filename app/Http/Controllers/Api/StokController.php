<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;

class StokController extends Controller
{
    /**
     * Menampilkan seluruh data stok barang.
     */
    public function index()
    {
        $barang = Barang::with([
            'kategori',
            'merk',
            'satuan',
            'stok',
        ])
        ->where('status', true)
        ->orderBy('nama_barang')
        ->get();

        $data = $barang->map(function ($item) {
            $stok = (float) ($item->stok?->jumlah ?? 0);
            $stokMinimum = (float) $item->stok_minimum;

            if ($stok <= 0) {
                $status = 'habis';
            } elseif ($stok <= $stokMinimum) {
                $status = 'menipis';
            } else {
                $status = 'aman';
            }

            return [
                'id' => $item->id,
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,

                'kategori' => $item->kategori?->nama_kategori,
                'merk' => $item->merk?->nama_merk,

                // PERBAIKAN:
                // gunakan nama_satuan, bukan simbol
                'satuan' => $item->satuan?->nama_satuan,

                'harga_beli' => $item->harga_beli,
                'harga_jual' => $item->harga_jual,

                'stok' => $stok,
                'stok_minimum' => $stokMinimum,
                'status_stok' => $status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data stok berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * Menampilkan detail stok satu barang.
     */
    public function show(int $barangId)
    {
        $item = Barang::with([
            'kategori',
            'merk',
            'satuan',
            'stok',
        ])->findOrFail($barangId);

        $stok = (float) ($item->stok?->jumlah ?? 0);
        $stokMinimum = (float) $item->stok_minimum;

        if ($stok <= 0) {
            $status = 'habis';
        } elseif ($stok <= $stokMinimum) {
            $status = 'menipis';
        } else {
            $status = 'aman';
        }

        return response()->json([
            'success' => true,
            'message' => 'Data stok berhasil diambil.',
            'data' => [
                'id' => $item->id,
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,

                'kategori' => $item->kategori?->nama_kategori,
                'merk' => $item->merk?->nama_merk,

                // PERBAIKAN:
                // gunakan nama_satuan, bukan simbol
                'satuan' => $item->satuan?->nama_satuan,

                'harga_beli' => $item->harga_beli,
                'harga_jual' => $item->harga_jual,

                'stok' => $stok,
                'stok_minimum' => $stokMinimum,
                'status_stok' => $status,
            ],
        ]);
    }
}