<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Kategori;
use App\Models\Merk;
use App\Models\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class BarangController extends Controller
{
    /**
     * Menampilkan semua barang aktif
     */
    public function index(): JsonResponse
    {
        $barang = Barang::with([
            'kategori',
            'merk',
            'satuan',
        ])
        ->where('status', true)
        ->orderBy('nama_barang')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diambil',
            'data' => $barang,
        ]);
    }

    /**
     * Menampilkan detail satu barang
     */
    public function show(int $id): JsonResponse
    {
        $barang = Barang::with([
            'kategori',
            'merk',
            'satuan',
            'stok',
        ])->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail barang berhasil diambil',
            'data' => $barang,
        ]);
    }

    /**
     * Menambahkan barang baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_barang' => [
                'required',
                'string',
                'max:50',
                'unique:barang,kode_barang',
            ],

            'nama_barang' => [
                'required',
                'string',
                'max:255',
            ],

          'kategori_id' => [
    'nullable',
    'integer',
    'exists:kategori,id',
],

'kategori_nama' => [
    'nullable',
    'string',
    'max:100',
],

            'merk_id' => [
    'nullable',
    'integer',
    'exists:merk,id',
],

'merk_nama' => [
    'nullable',
    'string',
    'max:100',
],

          'satuan_id' => [
    'nullable',
    'integer',
    'exists:satuan,id',
],

'satuan_nama' => [
    'nullable',
    'string',
    'max:100',
],

            'harga_beli' => [
                'required',
                'numeric',
                'min:0',
            ],

            'harga_jual' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stok_minimum' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stok_awal' => [
    'required',
    'numeric',
    'min:0',
],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'foto' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (
    empty($validated['kategori_id']) &&
    !empty($validated['kategori_nama'])
) {
    $kategori = Kategori::firstOrCreate(
        [
            'nama_kategori' => trim($validated['kategori_nama']),
        ],
        [
            'keterangan' => null,
        ]
    );

    $validated['kategori_id'] = $kategori->id;
}

unset($validated['kategori_nama']);

if (
    empty($validated['merk_id']) &&
    !empty($validated['merk_nama'])
) {
    $merk = Merk::firstOrCreate(
        [
            'nama_merk' => trim($validated['merk_nama']),
        ],
        [
            'keterangan' => null,
        ]
    );

    $validated['merk_id'] = $merk->id;
}

unset($validated['merk_nama']);

if (
    empty($validated['satuan_id']) &&
    !empty($validated['satuan_nama'])
) {
    $satuan = Satuan::firstOrCreate(
        [
            'nama_satuan' => trim($validated['satuan_nama']),
        ],
        [
            'simbol' => null,
            'keterangan' => null,
        ]
    );

    $validated['satuan_id'] = $satuan->id;
}

unset($validated['satuan_nama']);

        $validated['status'] = $validated['status'] ?? true;

       $stokAwal = $validated['stok_awal'];
unset($validated['stok_awal']);

$barang = Barang::create($validated);

// Buat stok awal sesuai input
$barang->stok()->create([
    'jumlah' => $stokAwal,
]);

        $barang->load([
            'kategori',
            'merk',
            'satuan',
            'stok',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang,
        ], 201);
    }

    public function importSheets(Request $request): JsonResponse
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
    ]);

    $filePath = $request->file('file')->getRealPath();

    $reader = IOFactory::createReaderForFile($filePath);

    $sheets = $reader->listWorksheetNames($filePath);

    return response()->json([
        'success' => true,
        'sheets' => $sheets,
    ]);
}

public function previewExcel(Request $request): JsonResponse
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        'sheet' => ['required', 'string'],
    ]);

    $filePath = $request->file('file')->getRealPath();

    $reader = IOFactory::createReaderForFile($filePath);

    // Hanya membaca Sheet yang dipilih
    $reader->setLoadSheetsOnly([$request->sheet]);

    // Tidak membaca style/formula yang tidak diperlukan untuk preview
    $reader->setReadDataOnly(true);

    // Batasi pembacaan Preview hanya 100 baris pertama.
    $readFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
        public function readCell(
            $column,
            $row,
            $worksheetName = ''
        ): bool {
            return $row <= 100;
        }

        public function readEmptyCells(): bool
        {
            return false;
        }
    };

    $reader->setReadFilter($readFilter);

    $spreadsheet = $reader->load($filePath);

    $worksheet = $spreadsheet->getSheetByName($request->sheet);

    if (!$worksheet) {
        return response()->json([
            'success' => false,
            'message' => 'Sheet Excel tidak ditemukan.',
        ], 422);
    }

    $preview = $worksheet->toArray(
        null,
        true,
        true,
        false
    );

    return response()->json([
        'success' => true,
        'sheet' => $request->sheet,
        'data' => $preview,
    ]);
}

public function importExcel(Request $request): JsonResponse
{
    $request->validate([
        'file' => [
            'required',
            'file',
            'mimes:xlsx,xls',
            'max:10240',
        ],
        'kategori_id' => [
            'required',
            'integer',
            'exists:kategori,id',
        ],
        'merk_id' => [
            'required',
            'integer',
            'exists:merk,id',
        ],
        'satuan_id' => [
            'required',
            'integer',
            'exists:satuan,id',
        ],
        'sheet' => [
            'required',
            'string',
        ],
    ]);

  Excel::import(
    new BarangImport(
        $request->kategori_id,
        $request->merk_id,
        $request->satuan_id,
        $request->sheet
    ),
    $request->file('file')
);

    return response()->json([
        'success' => true,
        'message' => 'Data barang berhasil diimport dari Excel.',
    ]);
}

    /**
     * Mengubah data barang
     */
public function update(Request $request, int $id): JsonResponse
{
    $barang = Barang::find($id);

    if (!$barang) {
        return response()->json([
            'success' => false,
            'message' => 'Barang tidak ditemukan',
        ], 404);
    }

    $validated = $request->validate([
        'kode_barang' => [
            'required',
            'string',
            'max:50',
            Rule::unique('barang', 'kode_barang')
                ->ignore($barang->id),
        ],

        'nama_barang' => [
            'required',
            'string',
            'max:255',
        ],

        'kategori_id' => [
            'nullable',
            'integer',
            'exists:kategori,id',
        ],

        'kategori_nama' => [
            'nullable',
            'string',
            'max:100',
        ],

        'merk_id' => [
            'nullable',
            'integer',
            'exists:merk,id',
        ],

        'merk_nama' => [
            'nullable',
            'string',
            'max:100',
        ],

        'satuan_id' => [
            'nullable',
            'integer',
            'exists:satuan,id',
        ],

        'satuan_nama' => [
            'nullable',
            'string',
            'max:100',
        ],

        'harga_beli' => [
            'required',
            'numeric',
            'min:0',
        ],

        'harga_jual' => [
            'required',
            'numeric',
            'min:0',
        ],

        'stok_minimum' => [
            'required',
            'numeric',
            'min:0',
        ],

        'deskripsi' => [
            'nullable',
            'string',
        ],

        'foto' => [
            'nullable',
            'string',
            'max:255',
        ],

        'status' => [
            'nullable',
            'boolean',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    if (
        empty($validated['kategori_id']) &&
        !empty($validated['kategori_nama'])
    ) {
        $kategori = Kategori::firstOrCreate(
            [
                'nama_kategori' => trim($validated['kategori_nama']),
            ],
            [
                'keterangan' => null,
            ]
        );

        $validated['kategori_id'] = $kategori->id;
    }

    unset($validated['kategori_nama']);

    /*
    |--------------------------------------------------------------------------
    | MERK
    |--------------------------------------------------------------------------
    */

    if (
        empty($validated['merk_id']) &&
        !empty($validated['merk_nama'])
    ) {
        $merk = Merk::firstOrCreate(
            [
                'nama_merk' => trim($validated['merk_nama']),
            ],
            [
                'keterangan' => null,
            ]
        );

        $validated['merk_id'] = $merk->id;
    }

    unset($validated['merk_nama']);

    /*
    |--------------------------------------------------------------------------
    | SATUAN
    |--------------------------------------------------------------------------
    */

    if (
        empty($validated['satuan_id']) &&
        !empty($validated['satuan_nama'])
    ) {
        $satuan = Satuan::firstOrCreate(
            [
                'nama_satuan' => trim($validated['satuan_nama']),
            ],
            [
                'simbol' => null,
                'keterangan' => null,
            ]
        );

        $validated['satuan_id'] = $satuan->id;
    }

    unset($validated['satuan_nama']);

    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG
    |--------------------------------------------------------------------------
    */

    $barang->update($validated);

    /*
    |--------------------------------------------------------------------------
    | AMBIL ULANG DATA BESERTA RELASI
    |--------------------------------------------------------------------------
    */

    $barang->load([
        'kategori',
        'merk',
        'satuan',
        'stok',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Barang berhasil diperbarui',
        'data' => $barang,
    ]);
}
    /**
     * Menghapus barang
     */
    public function destroy(int $id): JsonResponse
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        /*
         * Jangan hapus barang yang sudah memiliki
         * riwayat stok atau transaksi.
         */
        if (
            $barang->stokMutasi()->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak dapat dihapus karena sudah memiliki riwayat stok.',
            ], 422);
        }

        $barang->stok()?->delete();

        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ]);
    }
}