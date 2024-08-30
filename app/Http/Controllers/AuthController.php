<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // register
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->storeAs('public/users', $image->hashName());

            // Buat pengguna baru
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request['password']),
                'fullname' => $request->fullname ?? '',
                'address' => $request->address ?? '',
                'phone_number' => $request->phone_number ?? '',
                'image' => $image->hashName(),
                'role' => 'customer',
            ]);

        } else {

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request['password']),
                'fullname' => $request->fullname ?? '',
                'address' => $request->address ?? '',
                'phone_number' => $request->phone_number ?? '',
                'image' => $request->image ?? '',
                'role' => 'customer' ?? '',
            ]);

        }


        // Mengembalikan respons
        return new MasterResource(true, 'Data user berhasil ditambahkan', $user);   //memunculkan data dengan bantuan MasterResource
        // return response()->json(['message' => 'Berhasil Register!'], 201); //hanya memunculkan data berhasil
    }

    // login
    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid Password'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'message' => 'Berhasil Login!',
        ]);
    }

    // log out
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // hapus token pengguna
            $user->tokens()->delete();

            return response()->json(['message' => 'Logout Berhasil!'], 200);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
    }
}
