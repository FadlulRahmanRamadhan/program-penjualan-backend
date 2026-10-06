<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BarangKeluarController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Menampilkan daftar barang yang tersedia.
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

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diambil.',
            'data' => $barang,
        ]);
    }

    /**
     * Menyimpan transaksi barang keluar.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => [
                'required',
                'integer',
                'exists:barang,id',
            ],

            'jumlah' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'referensi' => [
                'nullable',
                'string',
                'max:100',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {
            $stok = $this->inventoryService->keluar(
                barangId: (int) $validated['barang_id'],
                jumlah: (float) $validated['jumlah'],
                referensi: $validated['referensi'] ?? null,
                keterangan: $validated['keterangan'] ?? null
            );

            $stok->load([
                'barang.kategori',
                'barang.merk',
                'barang.satuan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil disimpan.',
                'data' => [
                    'stok' => $stok,
                    'barang' => $stok->barang,
                ],
            ], 201);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang keluar gagal disimpan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail stok berdasarkan barang.
     */
    public function show(int $barangId)
    {
        $barang = Barang::with([
            'kategori',
            'merk',
            'satuan',
            'stok',
        ])->findOrFail($barangId);

        return response()->json([
            'success' => true,
            'message' => 'Data stok berhasil diambil.',
            'data' => $barang,
        ]);
    }
}