<?php

namespace App\Http\Controllers\Api;

use App\Services\PembayaranPenjualanService;
use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Services\PenjualanService;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with([
            'pelanggan',
            'detail.barang',
            'pembayaran',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'no_transaksi',
                    'like',
                    "%{$search}%"
                )->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                    $pelanggan
                        ->where(
                            'nama_pelanggan',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'kode_pelanggan',
                            'like',
                            "%{$search}%"
                        );
                });
            });
        }

        $data = $query
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(
        Request $request,
        PenjualanService $penjualanService
    ) {
        $validated = $request->validate([
            'tanggal' => [
                'nullable',
                'date',
            ],

            'pelanggan_id' => [
                'nullable',
                'integer',
                'exists:pelanggan,id',
            ],

            'diskon' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'pajak' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'biaya_lain' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'jatuh_tempo' => [
                'nullable',
                'date',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'detail' => [
                'required',
                'array',
                'min:1',
            ],

            'detail.*.barang_id' => [
                'required',
                'integer',
                'exists:barang,id',
            ],

            'detail.*.qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'detail.*.harga' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'detail.*.diskon' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'pembayaran.jumlah' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'pembayaran.metode' => [
                'nullable',
                'in:tunai,transfer,qris,debit,kredit',
            ],

            'pembayaran.referensi' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $penjualan = $penjualanService->buatPenjualan(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi penjualan berhasil dibuat.',
            'data' => $penjualan,
        ], 201);
    }

    public function bayarPiutang(
    Request $request,
    $id,
    PembayaranPenjualanService $pembayaranService
) {
    $validated = $request->validate([
        'jumlah' => [
            'required',
            'numeric',
            'gt:0',
        ],
        'metode' => [
            'required',
            'in:tunai,transfer,qris,debit,kredit',
        ],
        'referensi' => [
            'nullable',
            'string',
            'max:255',
        ],
        'keterangan' => [
            'nullable',
            'string',
        ],
    ]);

    $penjualan = $pembayaranService->bayar(
        penjualanId: (int) $id,
        jumlah: (float) $validated['jumlah'],
        metode: $validated['metode'],
        referensi: $validated['referensi'] ?? null,
        keterangan: $validated['keterangan'] ?? null
    );

    return response()->json([
        'success' => true,
        'message' => 'Pembayaran piutang berhasil disimpan.',
        'data' => $penjualan,
    ]);
}

    public function show($id)
    {
        $penjualan = Penjualan::with([
            'pelanggan',
            'detail.barang',
            'pembayaran',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $penjualan,
        ]);
    }
}