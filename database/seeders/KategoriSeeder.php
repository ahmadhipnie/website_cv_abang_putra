<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop untuk membuat data kategori dari 1 sampai 6
        for ($i = 1; $i <= 6; $i++) {
            DB::table('kategoris')->insert([
                'id_kategori' => $i, // ID kategori
                'nama_kategori' => 'kategori ' . $i, // Nama kategori
                'image_url' => 'storage/foto_kategori/kategori_' . $i . '.png', // URL gambar kategori
            ]);
        }
    }
}
