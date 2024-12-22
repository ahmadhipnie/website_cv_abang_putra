<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promos = [];

        // Loop untuk membuat data promo
        for ($i = 1; $i <= 3; $i++) {
            $startDate = Carbon::now()->subDays(rand(1, 30))->format('Y/m/d'); // Tanggal awal acak
            $endDate = Carbon::now()->addDays(rand(1, 30))->format('Y/m/d'); // Tanggal akhir acak

            $promos[] = [
                'id_promo' => $i, // ID promo
                'nama_promo' => 'promo ' . $i, // Nama promo
                'deskripsi_promo' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', // Deskripsi dummy
                'tanggal_periode_awal' => $startDate, // Tanggal periode awal
                'tanggal_periode_akhir' => $endDate, // Tanggal periode akhir
                'created_at' => Carbon::now()->toDateTimeString(), // Timestamp created_at
                'updated_at' => Carbon::now()->toDateTimeString(), // Timestamp updated_at
            ];
        }

        // Masukkan data ke tabel
        DB::table('promos')->insert($promos);
    }
}
