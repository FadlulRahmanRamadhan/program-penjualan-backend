<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_penjualan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penjualan_id')
                ->constrained('penjualan')
                ->cascadeOnDelete();

            $table->dateTime('tanggal_bayar');

            $table->decimal('jumlah', 15, 2);

            $table->enum('metode', [
                'tunai',
                'transfer',
                'qris',
                'debit',
                'kredit'
            ])->default('tunai');

            $table->string('referensi')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_penjualan');
    }
};