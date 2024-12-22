<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ResellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('resellers')->insert([
            'id_reseller' => '1',
            'nama' => 'asd',
            'alamat' => 'jember',
            'nomor_telepon' => '1233',
            'tanggal_lahir' => '2024-12-12',
            'foto_profil' => 'storage/foto_profil_reseller/foto_profil_asd.jpeg',
            'user_id' => '2',

        ]);
    }
}
