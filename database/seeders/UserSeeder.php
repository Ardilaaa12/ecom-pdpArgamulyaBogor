<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\User;
use App\Models\Cart;
use App\Models\Like;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
=======
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
>>>>>>> d9a5499fbd9d0f26488be66e05e193b2178f3bd5

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
<<<<<<< HEAD
            'username'  => 'adminPdp',
            'email'     => 'admin01@gmail.com',
            'password'  => 'Kazuha12*',
            'role'      => 'admin',
            'is_verified' => 1,
        ]);

        User::create([
            'username'  => 'customerPdp',
            'email'     => 'customer01@gmail.com',
            'password'  => Hash::make('Kazuha12*'),
            'role'      => 'customer',
            'is_verified' => 1,
        ]);

        Cart::create([
            'user_id' => 2,
        ]);

        Like::create([
            'user_id' => 2,
        ]);
=======
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
>>>>>>> d9a5499fbd9d0f26488be66e05e193b2178f3bd5
    }
}
