<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_pelanggan', 'like', "%{$search}%")
                    ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $data = $query
            ->orderBy('nama_pelanggan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_pelanggan' => [
                'required',
                'string',
                'max:50',
                'unique:pelanggan,kode_pelanggan',
            ],
            'nama_pelanggan' => [
                'required',
                'string',
                'max:255',
            ],
            'no_hp' => [
                'nullable',
                'string',
                'max:30',
            ],
            'alamat' => [
                'nullable',
                'string',
            ],
            'limit_piutang' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['limit_piutang'] = $validated['limit_piutang'] ?? 0;
        $validated['status'] = $validated['status'] ?? true;

        $pelanggan = Pelanggan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'data' => $pelanggan,
        ], 201);
    }

    public function show($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $validated = $request->validate([
            'kode_pelanggan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pelanggan', 'kode_pelanggan')
                    ->ignore($pelanggan->id),
            ],
            'nama_pelanggan' => [
                'required',
                'string',
                'max:255',
            ],
            'no_hp' => [
                'nullable',
                'string',
                'max:30',
            ],
            'alamat' => [
                'nullable',
                'string',
            ],
            'limit_piutang' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $pelanggan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui.',
            'data' => $pelanggan->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}