<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratJalanController extends Controller
{
    /**
     * Menampilkan daftar Surat Jalan.
     */
    public function index(Request $request)
    {
        $query = SuratJalan::with([
            'penjualan.pelanggan',
            'penjualan.detail.barang',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('no_surat_jalan', 'like', "%{$search}%")
                    ->orWhere('penerima', 'like', "%{$search}%")
                    ->orWhereHas('penjualan', function ($penjualan) use ($search) {
                        $penjualan
                            ->where('no_transaksi', 'like', "%{$search}%")
                            ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                                $pelanggan
                                    ->where('nama_pelanggan', 'like', "%{$search}%")
                                    ->orWhere('kode_pelanggan', 'like', "%{$search}%");
                            });
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

    /**
     * Membuat Surat Jalan dari transaksi penjualan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penjualan_id' => [
                'required',
                'integer',
                'exists:penjualan,id',
            ],

            'tanggal' => [
                'required',
                'date',
            ],

            'alamat_pengiriman' => [
                'nullable',
                'string',
            ],

            'penerima' => [
                'nullable',
                'string',
                'max:255',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],
        ]);

        $penjualan = Penjualan::with([
            'pelanggan',
            'detail.barang',
        ])->findOrFail($validated['penjualan_id']);

        /*
        |--------------------------------------------------------------------------
        | Cegah Surat Jalan ganda
        |--------------------------------------------------------------------------
        */

        $sudahAda = SuratJalan::where(
            'penjualan_id',
            $penjualan->id
        )->exists();

        if ($sudahAda) {
            return response()->json([
                'success' => false,
                'message' => 'Surat Jalan untuk transaksi ini sudah dibuat.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate nomor Surat Jalan
        |--------------------------------------------------------------------------
        */

        $tanggal = now()->format('Ymd');

        $jumlahHariIni = SuratJalan::whereDate(
            'created_at',
            now()->toDateString()
        )->count();

        $nomor = str_pad(
            $jumlahHariIni + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $noSuratJalan = 'SJ-' . $tanggal . '-' . $nomor;

        /*
        |--------------------------------------------------------------------------
        | Jika alamat tidak diisi, ambil dari pelanggan
        |--------------------------------------------------------------------------
        */

        $alamat = $validated['alamat_pengiriman']
            ?? optional($penjualan->pelanggan)->alamat;

        /*
        |--------------------------------------------------------------------------
        | Jika penerima tidak diisi, gunakan nama pelanggan
        |--------------------------------------------------------------------------
        */

        $penerima = $validated['penerima']
            ?? optional($penjualan->pelanggan)->nama_pelanggan;

        /*
        |--------------------------------------------------------------------------
        | Simpan Surat Jalan
        |--------------------------------------------------------------------------
        */

        $suratJalan = DB::transaction(function () use (
            $validated,
            $penjualan,
            $noSuratJalan,
            $alamat,
            $penerima
        ) {
            return SuratJalan::create([
                'no_surat_jalan' => $noSuratJalan,
                'penjualan_id' => $penjualan->id,
                'tanggal' => $validated['tanggal'],
                'alamat_pengiriman' => $alamat,
                'penerima' => $penerima,
                'status' => 'draft',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        $suratJalan->load([
            'penjualan.pelanggan',
            'penjualan.detail.barang',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat Jalan berhasil dibuat.',
            'data' => $suratJalan,
        ], 201);
    }

    /**
     * Menampilkan satu Surat Jalan.
     */
    public function show($id)
    {
        $suratJalan = SuratJalan::with([
            'penjualan.pelanggan',
            'penjualan.detail.barang',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $suratJalan,
        ]);
    }

    /**
     * Mengubah status Surat Jalan.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:draft,dikirim,selesai,dibatalkan',
            ],
        ]);

        $suratJalan = SuratJalan::findOrFail($id);

        $suratJalan->update([
            'status' => $validated['status'],
        ]);

        $suratJalan->load([
            'penjualan.pelanggan',
            'penjualan.detail.barang',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status Surat Jalan berhasil diperbarui.',
            'data' => $suratJalan,
        ]);
    }

    /**
     * Menghapus Surat Jalan.
     */
    public function destroy($id)
    {
        $suratJalan = SuratJalan::findOrFail($id);

        $suratJalan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat Jalan berhasil dihapus.',
        ]);
    }
}