<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(NavbarSeeder::class);
        $this->call(CategorieSeeder::class);
        $this->call(ShippingCostSeeder::class);

        // cara run di terminal : php artisan db:seed
        // ulang upload seeder : php artisan migrate:refresh --seed
    }
}
