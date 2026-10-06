<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // KATEGORI
        // =========================

        $plafon = Kategori::create([
            'nama_kategori' => 'Plafon',
            'keterangan' => 'Produk plafon',
        ]);

        $cat = Kategori::create([
            'nama_kategori' => 'Cat',
            'keterangan' => 'Produk cat',
        ]);

        $semen = Kategori::create([
            'nama_kategori' => 'Semen',
            'keterangan' => 'Produk semen',
        ]);


        // =========================
        // MERK
        // =========================

        $kimi = Merk::create([
            'nama_merk' => 'KIMI-MDN',
            'keterangan' => 'Merk contoh',
        ]);

        $dulux = Merk::create([
            'nama_merk' => 'Dulux',
            'keterangan' => 'Merk cat',
        ]);

        $tigaRoda = Merk::create([
            'nama_merk' => 'Tiga Roda',
            'keterangan' => 'Merk semen',
        ]);


        // =========================
        // SATUAN
        // =========================

        $lbr = Satuan::create([
            'nama_satuan' => 'Lembar',
            'simbol' => 'LBR',
        ]);

        $pcs = Satuan::create([
            'nama_satuan' => 'Pieces',
            'simbol' => 'PCS',
        ]);

        $kg = Satuan::create([
            'nama_satuan' => 'Kilogram',
            'simbol' => 'KG',
        ]);


        // =========================
        // BARANG
        // =========================

        Barang::create([
            'kode_barang' => 'PVCM001',
            'nama_barang' => 'KIMI PVC 8',
            'kategori_id' => $plafon->id,
            'merk_id' => $kimi->id,
            'satuan_id' => $lbr->id,
            'harga_beli' => 30000,
            'harga_jual' => 38000,
            'stok_minimum' => 10,
            'deskripsi' => 'Plafon PVC KIMI ukuran 8',
            'status' => true,
        ]);

        Barang::create([
            'kode_barang' => 'CAT001',
            'nama_barang' => 'Dulux Putih 5 KG',
            'kategori_id' => $cat->id,
            'merk_id' => $dulux->id,
            'satuan_id' => $kg->id,
            'harga_beli' => 85000,
            'harga_jual' => 110000,
            'stok_minimum' => 5,
            'deskripsi' => 'Cat tembok putih',
            'status' => true,
        ]);

        Barang::create([
            'kode_barang' => 'SMN001',
            'nama_barang' => 'Semen Tiga Roda',
            'kategori_id' => $semen->id,
            'merk_id' => $tigaRoda->id,
            'satuan_id' => $kg->id,
            'harga_beli' => 50000,
            'harga_jual' => 65000,
            'stok_minimum' => 10,
            'deskripsi' => 'Semen Portland',
            'status' => true,
        ]);
    }
}