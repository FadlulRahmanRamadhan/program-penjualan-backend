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
    Schema::create('penjualan', function (Blueprint $table) {
        $table->id();

        $table->string('no_transaksi')->unique();
        $table->dateTime('tanggal');

        $table->foreignId('pelanggan_id')
            ->nullable()
            ->constrained('pelanggan')
            ->nullOnDelete();

        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('diskon', 15, 2)->default(0);
        $table->decimal('pajak', 15, 2)->default(0);
        $table->decimal('biaya_lain', 15, 2)->default(0);
        $table->decimal('total', 15, 2)->default(0);

        $table->decimal('dibayar', 15, 2)->default(0);
        $table->decimal('piutang', 15, 2)->default(0);

        $table->date('jatuh_tempo')->nullable();

        $table->enum('status', [
            'draft',
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
        Schema::dropIfExists('penjualan');
    }
};
