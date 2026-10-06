<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | DATA STOK
        |--------------------------------------------------------------------------
        */

        $totalBarang = Barang::where('status', true)->count();

        $totalStok = Stok::sum('jumlah');

        $stokHabis = Stok::where('jumlah', '<=', 0)->count();

        $stokMenipis = Stok::where('jumlah', '>', 0)
            ->whereHas('barang', function ($query) {
                $query->whereColumn('stok.jumlah', '<=', 'barang.stok_minimum');
            })
            ->count();

        $stokAman = max(
            0,
            $totalBarang - $stokHabis - $stokMenipis
        );

        /*
        |--------------------------------------------------------------------------
        | PENJUALAN
        |--------------------------------------------------------------------------
        */

        $penjualanHariIni = Penjualan::whereDate(
            'tanggal',
            now()->toDateString()
        )
            ->where('status', 'selesai')
            ->sum('total');

        $transaksiHariIni = Penjualan::whereDate(
            'tanggal',
            now()->toDateString()
        )
            ->where('status', 'selesai')
            ->count();

        $penjualanBulanIni = Penjualan::whereYear(
            'tanggal',
            now()->year
        )
            ->whereMonth(
                'tanggal',
                now()->month
            )
            ->where('status', 'selesai')
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | PIUTANG
        |--------------------------------------------------------------------------
        */

        $totalPiutang = Penjualan::where('status', 'selesai')
            ->sum('piutang');

        /*
        |--------------------------------------------------------------------------
        | PELANGGAN
        |--------------------------------------------------------------------------
        */

        $totalPelanggan = Pelanggan::where('status', true)->count();

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TERBARU
        |--------------------------------------------------------------------------
        */

        $transaksiTerbaru = Penjualan::with('pelanggan')
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_transaksi' => $item->no_transaksi,
                    'tanggal' => $item->tanggal,
                    'pelanggan' => $item->pelanggan?->nama_pelanggan ?? 'Umum',
                    'total' => (float) $item->total,
                    'dibayar' => (float) $item->dibayar,
                    'piutang' => (float) $item->piutang,
                    'status' => $item->status,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | BARANG STOK MENIPIS
        |--------------------------------------------------------------------------
        */

        $barangStokMenipis = Barang::with('stok')
            ->where('status', true)
            ->whereHas('stok', function ($query) {
                $query->whereColumn(
                    'stok.jumlah',
                    '<=',
                    'barang.stok_minimum'
                );
            })
            ->orderBy('nama_barang')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $jumlah = (float) ($item->stok?->jumlah ?? 0);

                return [
                    'id' => $item->id,
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'stok' => $jumlah,
                    'stok_minimum' => (float) $item->stok_minimum,
                    'status_stok' => $jumlah <= 0
                        ? 'habis'
                        : 'menipis',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'Dashboard berhasil diambil.',
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
            ],
            'data' => [
                'ringkasan' => [
                    'total_barang' => $totalBarang,
                    'total_stok' => (float) $totalStok,
                    'stok_aman' => $stokAman,
                    'stok_menipis' => $stokMenipis,
                    'stok_habis' => $stokHabis,
                    'penjualan_hari_ini' => (float) $penjualanHariIni,
                    'transaksi_hari_ini' => $transaksiHariIni,
                    'penjualan_bulan_ini' => (float) $penjualanBulanIni,
                    'total_piutang' => (float) $totalPiutang,
                    'total_pelanggan' => $totalPelanggan,
                ],

                'transaksi_terbaru' => $transaksiTerbaru,

                'barang_stok_menipis' => $barangStokMenipis,
            ],
        ]);
    }
}