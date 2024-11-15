<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'Admin PDP',
            'email' => 'pdpargamulyabogor@gmail.com',
            'password' => Hash::make('Kazuha12*'),
            'role' => 'admin',
            'is_verified' => 1,
        ]);

        // User::create([
        //     'username' => 'Admin PDP',
        //     'email' => 'pdpargamulyabogor@gmail.com',
        //     'password' => Hash::make('Kazuha12*'),
        //     'role' => 'admin',
        //     'is_verified' => 1,
        // ]);
    }
}
