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
    Schema::create('penjualan_detail', function (Blueprint $table) {
        $table->id();

        $table->foreignId('penjualan_id')
            ->constrained('penjualan')
            ->cascadeOnDelete();

        $table->foreignId('barang_id')
            ->constrained('barang')
            ->restrictOnDelete();

        $table->decimal('qty', 15, 2);
        $table->decimal('harga', 15, 2);
        $table->decimal('diskon', 15, 2)->default(0);
        $table->decimal('subtotal', 15, 2);

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
