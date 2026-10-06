<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function kategori(): JsonResponse
    {
        $data = Kategori::orderBy('nama_kategori')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function storeKategori(Request $request): JsonResponse
{
    $validated = $request->validate([
        'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori',
        'keterangan' => 'nullable|string|max:255',
    ]);

    $kategori = Kategori::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan.',
        'data' => $kategori,
    ], 201);
}

public function updateKategori(Request $request, $id): JsonResponse
{
    $kategori = Kategori::findOrFail($id);

    $validated = $request->validate([
        'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori,' . $id,
        'keterangan' => 'nullable|string|max:255',
    ]);

    $kategori->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Kategori berhasil diperbarui.',
        'data' => $kategori->fresh(),
    ]);
}

public function destroyKategori($id): JsonResponse
{
    $kategori = Kategori::findOrFail($id);

    // Cek apakah kategori sudah digunakan oleh barang
    if ($kategori->barang()->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak dapat dihapus karena sudah digunakan oleh data barang.',
        ], 422);
    }

    $kategori->delete();

    return response()->json([
        'success' => true,
        'message' => 'Kategori berhasil dihapus.',
    ]);
}

    public function merk(): JsonResponse
    {
        $data = Merk::orderBy('nama_merk')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data merk berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function satuan(): JsonResponse
    {
        $data = Satuan::orderBy('nama_satuan')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data satuan berhasil diambil.',
            'data' => $data,
        ]);
    }
}