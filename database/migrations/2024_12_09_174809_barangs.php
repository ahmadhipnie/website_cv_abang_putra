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
        Schema::create('barangs', function (Blueprint $table) {
            $table->bigIncrements('id_barang');
            $table->string('nama_barang');
            $table->integer('harga_barang');
            $table->integer('stok_barang');
            $table->text('deskripsi_barang');
            $table->enum('satuan', ['Pax', 'Bungkus', 'Lusin', 'Kodi', 'Pcs', 'Box'])->nullable(false);
            $table->unsignedBigInteger('kategori_id');

            $table->foreign('kategori_id')->references('id_kategori')->on('kategoris')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
