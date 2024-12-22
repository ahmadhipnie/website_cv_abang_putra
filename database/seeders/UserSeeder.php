<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id_user' => '1',
            'email' => 'a@gmail.com',
            'password' => '123',
            'role' => 'admin',
        ]);
        DB::table('users')->insert([
            'id_user' => '2',
            'email' => 'b@gmail.com',
            'password' => '123',
            'role' => 'reseller',
        ]);
    }
}
