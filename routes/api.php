<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\BarangKeluarController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BarangMasukController;
use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\SuratJalanController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', [AuthController::class, 'user'])
    ->middleware('auth:sanctum');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

    // Manajemen User - khusus Admin
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::post('/users', [UserController::class, 'store'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::get('/users/{id}', [UserController::class, 'show'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::put('/users/{id}', [UserController::class, 'update'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::delete('/users/{id}', [UserController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'role:admin']);

/*
|--------------------------------------------------------------------------
| DATA BARANG
|--------------------------------------------------------------------------
| Admin, Kasir, Gudang, Owner
*/

Route::get('/barang', [BarangController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang,owner'
    ]);

    Route::post('/barang/import-sheets', [BarangController::class, 'importSheets'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::post('/barang/preview-excel', [BarangController::class, 'previewExcel'])
    ->middleware(['auth:sanctum', 'role:admin']);
    
    Route::post('/barang/import', [BarangController::class, 'importExcel'])
    ->middleware(['auth:sanctum', 'role:admin']);


Route::get('/barang/{id}', [BarangController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang,owner'
    ]);

Route::post('/barang', [BarangController::class, 'store'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::put('/barang/{id}', [BarangController::class, 'update'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::delete('/barang/{id}', [BarangController::class, 'destroy'])
    ->middleware([
        'auth:sanctum',
        'role:admin'
    ]);
/*
|--------------------------------------------------------------------------
| BARANG MASUK
|--------------------------------------------------------------------------
| Admin, Kasir, Gudang
*/

Route::get('/barang-masuk', [BarangMasukController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);

Route::post('/barang-masuk', [BarangMasukController::class, 'store'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);

Route::get('/barang-masuk/{id}', [BarangMasukController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);


/*
|--------------------------------------------------------------------------
| BARANG KELUAR
|--------------------------------------------------------------------------
| Admin, Kasir, Gudang
*/

Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);

Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);

Route::get('/barang-keluar/{id}', [BarangKeluarController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang'
    ]);


/*
|--------------------------------------------------------------------------
| PELANGGAN
|--------------------------------------------------------------------------
| Admin, Kasir
*/

Route::get('/pelanggan', [PelangganController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,owner'
    ]);

Route::post('/pelanggan', [PelangganController::class, 'store'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::get('/pelanggan/{id}', [PelangganController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);


/*
|--------------------------------------------------------------------------
| PENJUALAN
|--------------------------------------------------------------------------
| Admin, Kasir
| Owner hanya melihat data
*/

Route::get('/penjualan', [PenjualanController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,owner'
    ]);

Route::post('/penjualan', [PenjualanController::class, 'store'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir'
    ]);

Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,owner'
    ]);

Route::post(
    '/penjualan/{id}/pembayaran',
    [PenjualanController::class, 'bayarPiutang']
)->middleware([
    'auth:sanctum',
    'role:admin,kasir'
]);

// ==================== SURAT JALAN ====================

Route::get('/surat-jalan', [SuratJalanController::class, 'index'])
    ->middleware(['auth:sanctum','role:admin,kasir,owner']);

Route::post('/surat-jalan', [SuratJalanController::class, 'store'])
    ->middleware(['auth:sanctum','role:admin,kasir']);

Route::get('/surat-jalan/{id}', [SuratJalanController::class, 'show'])
    ->middleware(['auth:sanctum','role:admin,kasir,owner']);

Route::put('/surat-jalan/{id}/status', [SuratJalanController::class, 'updateStatus'])
    ->middleware(['auth:sanctum','role:admin,kasir']);

Route::delete('/surat-jalan/{id}', [SuratJalanController::class, 'destroy'])
    ->middleware(['auth:sanctum','role:admin']);


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,owner'
    ]);



    Route::get('/kategori', [MasterDataController::class, 'kategori'])
    ->middleware(['auth:sanctum', 'role:admin,kasir,gudang,owner']);

    Route::post('/kategori', [MasterDataController::class, 'storeKategori'])
    ->middleware(['auth:sanctum', 'role:admin,kasir']);

    Route::put('/kategori/{id}', [MasterDataController::class, 'updateKategori'])
    ->middleware(['auth:sanctum', 'role:admin,kasir']);

    Route::delete('/kategori/{id}', [MasterDataController::class, 'destroyKategori'])
    ->middleware(['auth:sanctum', 'role:admin,kasir']);

Route::get('/merk', [MasterDataController::class, 'merk'])
    ->middleware(['auth:sanctum', 'role:admin,kasir,gudang,owner']);

Route::get('/satuan', [MasterDataController::class, 'satuan'])
    ->middleware(['auth:sanctum', 'role:admin,kasir,gudang,owner']);

/*
|--------------------------------------------------------------------------
| STOK
|--------------------------------------------------------------------------
| Admin, Kasir, Gudang, Owner
*/

Route::get('/stok', [StokController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang,owner'
    ]);

Route::get('/stok/{barangId}', [StokController::class, 'show'])
    ->middleware([
        'auth:sanctum',
        'role:admin,kasir,gudang,owner'
    ]);