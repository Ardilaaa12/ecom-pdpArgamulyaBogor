<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ShippingCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shipping_costs')->insert([
            [
                'city' => 'Jakarta',
                'cost' => '100000',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city' => 'Bogor',
                'cost' => '50000',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city' => 'Lampung',
                'cost' => '200000',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city' => 'Bekasi',
                'cost' => '50000',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
