<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();

            $table->string('kode_barang')->unique();
            $table->string('nama_barang');

            $table->foreignId('kategori_id')
                ->constrained('kategori')
                ->restrictOnDelete();

            $table->foreignId('merk_id')
                ->constrained('merk')
                ->restrictOnDelete();

            $table->foreignId('satuan_id')
                ->constrained('satuan')
                ->restrictOnDelete();

            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);

            $table->integer('stok_minimum')->default(0);

            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};