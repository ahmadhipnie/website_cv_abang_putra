<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GambarBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageUrls = [
            'storage/foto_barang/img_barang_1.jpg',
            'storage/foto_barang/img_barang_2.jpg',
            'storage/foto_barang/img_barang_3.jpg',
            'storage/foto_barang/img_barang_4.jpg',
            'storage/foto_barang/img_barang_5.jpg',
            'storage/foto_barang/img_barang_6.jpg',
            'storage/foto_barang/img_barang_7.jpg',
            'storage/foto_barang/img_barang_8.jpg',
            'storage/foto_barang/img_barang_9.jpg',
            'storage/foto_barang/img_barang_10.jpg',
        ];

        $data = [];

        // Loop untuk membuat 40 data
        for ($i = 1; $i <= 40; $i++) {
            $data[] = [
                'id_gambar_barang' => $i, // ID gambar barang
                'gambar_url' => $imageUrls[($i - 1) % 10], // Pilih URL gambar secara berulang
                'barang_id' => (($i - 1) % 20) + 1, // Barang ID berulang dari 1 hingga 20
            ];
        }

        // Masukkan data ke tabel
        DB::table('gambar_barangs')->insert($data);
    }
}
