<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GambarPromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        // Loop untuk membuat 9 data gambar promo
        for ($i = 1; $i <= 9; $i++) {
            $data[] = [
                'id_gambar_promo' => $i, // ID gambar promo
                'gambar_url' => 'storage/foto_promo/img_promo_' . $i . '.jpg', // URL gambar promo
                'promo_id' => (($i - 1) % 3) + 1, // Promo ID berulang dari 1 hingga 3
            ];
        }

        // Masukkan data ke tabel
        DB::table('gambar_promos')->insert($data);
    }
}
