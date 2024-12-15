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
        Schema::create('gambar_barangs', function (Blueprint $table) {
            $table->bigIncrements('id_gambar_barang');
            $table->text('gambar_url');
            $table->unsignedBigInteger('barang_id');

            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gambar_barangs');
    }
};
