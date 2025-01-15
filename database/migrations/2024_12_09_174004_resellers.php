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
        Schema::create('resellers', function (Blueprint $table) {
            $table->bigIncrements('id_reseller');
            $table->string('nama');
            $table->string('alamat')->nullable(true);
            $table->string('nomor_telepon');
            $table->date('tanggal_lahir');
            $table->text('foto_profil')->nullable(false);
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};
