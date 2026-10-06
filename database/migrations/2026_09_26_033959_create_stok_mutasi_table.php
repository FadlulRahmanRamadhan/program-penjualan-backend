<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('barang')
                ->restrictOnDelete();

            $table->enum('tipe', [
                'masuk',
                'keluar',
                'penyesuaian'
            ]);

            $table->decimal('jumlah', 15, 2);

            $table->decimal('stok_sebelum', 15, 2)->default(0);
            $table->decimal('stok_sesudah', 15, 2)->default(0);

            $table->string('referensi')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};