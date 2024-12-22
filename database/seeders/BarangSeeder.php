<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create(); // Inisialisasi Faker untuk dummy data
        $satuanList = ['Pax', 'Bungkus', 'Lusin', 'Kodi', 'Pcs', 'Box']; // Pilihan satuan

        // Loop untuk membuat data 1 hingga 20
        for ($i = 1; $i <= 20; $i++) {
            DB::table('barangs')->insert([
                'id_barang' => $i, // ID barang
                'nama_barang' => 'barang ' . $i, // Nama barang
                'harga_barang' => rand(30000, 80000), // Harga barang antara 30000 dan 80000
                'stok_barang' => rand(1, 150), // Stok barang antara 1 dan 150
                'deskripsi_barang' => $faker->sentence(20), // Deskripsi barang dengan dummy text
                'satuan' => $satuanList[array_rand($satuanList)], // Pilihan satuan secara acak
                'kategori_id' => rand(1, 6), // Misal kategori acak dari 1 sampai 5
            ]);
        }
    }
}
