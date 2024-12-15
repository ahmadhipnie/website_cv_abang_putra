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
        Schema::create('gambar_promos', function (Blueprint $table) {
            $table->bigIncrements('id_gambar_promo');
            $table->text('gambar_url');
            $table->unsignedBigInteger('promo_id');

            $table->foreign('promo_id')->references('id_promo')->on('promos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gambar_promos');
    }
};
