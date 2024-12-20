<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class NavbarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('navbars')->insert([
            [
                'name' => 'dasboard',
                'route' => 'home/dasboard',
                'status' => 'active',
                'type' => 'text',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'testimoni',
                'route' => 'home/testimoni',
                'status' => 'active',
                'type' => 'text',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'penghargaan',
                'route' => 'home/penghargaan',
                'status' => 'active',
                'type' => 'text',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'produk',
                'route' => 'home/produk',
                'status' => 'active',
                'type' => 'text',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
