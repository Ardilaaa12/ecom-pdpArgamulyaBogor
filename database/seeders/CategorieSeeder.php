<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name_category' => 'jantan',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name_category' => 'betina',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
        ]);

    }
}
