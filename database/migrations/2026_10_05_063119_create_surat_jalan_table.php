<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_jalan', function (Blueprint $table) {
            $table->id();

            $table->string('no_surat_jalan')->unique();

            $table->foreignId('penjualan_id')
                ->unique()
                ->constrained('penjualan')
                ->cascadeOnDelete();

            $table->dateTime('tanggal');

            $table->string('alamat_pengiriman')->nullable();

            $table->string('penerima')->nullable();

            $table->enum('status', [
                'draft',
                'dikirim',
                'selesai',
                'dibatalkan',
            ])->default('draft');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalan');
    }
};