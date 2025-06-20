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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('jumlah_barang');
            $table->integer('total_harga');
            $table->enum('jenis_pengiriman', ['cod', 'ambil_ditempat']);
            $table->enum('status', ['diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('diproses');

            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};