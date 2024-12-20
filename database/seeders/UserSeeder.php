<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'adminPDP',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'), // Pastikan menggunakan Hash
                'fullname' => 'Admin PDP',
                'address' => 'Jl. Admin No.1',
                'phone_number' => '081234567890',
                'role' => 'admin',
                'verification_code' => null,
                'is_verified' => true,
                'created_at' => Carbon::now()->subMonth(), // Sebulan yang lalu
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'adminArgamulya',
                'email' => 'adminargamulya@gmail.com',
                'password' => Hash::make('password123'), // Pastikan menggunakan Hash
                'fullname' => 'Admin Argamulya',
                'address' => 'Jl. Admin No.1',
                'phone_number' => '081234567890',
                'role' => 'admin',
                'verification_code' => null,
                'is_verified' => true,
                'created_at' => Carbon::now()->subMonth(), // Sebulan yang lalu
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'Lala',
                'email' => 'lala@gmail.com',
                'password' => Hash::make('password123'),
                'fullname' => 'Customer Lala',
                'address' => 'Jl. Customer No.2',
                'phone_number' => '081234567891',
                'role' => 'customer',
                'verification_code' => '123456',
                'is_verified' => false,
                'created_at' => Carbon::now()->subMonth(), // Sebulan yang lalu
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'Dila',
                'email' => 'dila@gmail.com',
                'password' => Hash::make('password123'),
                'fullname' => 'Customer Dila',
                'address' => 'Jl. Customer No.2',
                'phone_number' => '081234567891',
                'role' => 'customer',
                'verification_code' => '123456',
                'is_verified' => false,
                'created_at' => Carbon::now()->subMonth(), // Sebulan yang lalu
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'Hana',
                'email' => 'hana@gmail.com',
                'password' => Hash::make('password123'),
                'fullname' => 'Customer Hana',
                'address' => 'Jl. Customer No.2',
                'phone_number' => '081234567891',
                'role' => 'customer',
                'verification_code' => '123456',
                'is_verified' => true,
                'created_at' => Carbon::now()->subMonth(), // Sebulan yang lalu
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('carts')->insert([
            [
                'user_id' => 3,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 4,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 5,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('likes')->insert([
            [
                'user_id' => 3,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 4,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 5,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
