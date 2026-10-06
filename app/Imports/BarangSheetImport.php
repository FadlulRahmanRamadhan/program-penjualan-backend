<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BarangSheetImport implements ToCollection, WithChunkReading
{
    protected $kategoriId;
    protected $merkId;
    protected $satuanId;

    public function __construct(
        $kategoriId,
        $merkId,
        $satuanId
    ) {
        $this->kategoriId = $kategoriId;
        $this->merkId = $merkId;
        $this->satuanId = $satuanId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Baris pertama adalah header
            if ($index === 0) {
                continue;
            }

            // Kolom Excel:
            // 0 = No
            // 1 = Nama Barang
            // 2 = @ / Harga Satuan
            // 3 = QTY
            // 4 = Harga Awal

            $namaBarang = trim((string) ($row[1] ?? ''));

            $harga = $row[2] ?? 0;

            $stokAwal = $row[3] ?? 0;

            // Lewati baris kosong
            if ($namaBarang === '') {
                continue;
            }

            // Bersihkan harga
            if (is_string($harga)) {
                $harga = preg_replace('/[^\d.,-]/', '', $harga);
                $harga = str_replace(',', '.', $harga);
            }

            $harga = is_numeric($harga)
                ? (float) $harga
                : 0;

            // Bersihkan stok
            if (is_string($stokAwal)) {
                $stokAwal = preg_replace('/[^\d.,-]/', '', $stokAwal);
                $stokAwal = str_replace(',', '.', $stokAwal);
            }

            $stokAwal = is_numeric($stokAwal)
                ? (float) $stokAwal
                : 0;

            // Cek duplikat
            $barang = Barang::where(
                'nama_barang',
                $namaBarang
            )->first();

            if ($barang) {
                continue;
            }

            // Buat kode barang
            $kodeBarang = $this->buatKodeBarang();

            // Simpan barang
            $barang = Barang::create([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'kategori_id' => $this->kategoriId,
                'merk_id' => $this->merkId,
                'satuan_id' => $this->satuanId,

                'harga_beli' => $harga,

                // Sementara harga jual mengikuti harga Excel
                'harga_jual' => $harga,

                'stok_minimum' => 0,
                'deskripsi' => null,
                'foto' => null,
                'status' => true,
            ]);

            // Simpan stok awal
            $barang->stok()->create([
                'jumlah' => $stokAwal,
            ]);
        }
    }

    protected function buatKodeBarang()
    {
        do {
            $kode = 'IMP' . str_pad(
                (string) (Barang::max('id') + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
        } while (
            Barang::where('kode_barang', $kode)->exists()
        );

        return $kode;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}