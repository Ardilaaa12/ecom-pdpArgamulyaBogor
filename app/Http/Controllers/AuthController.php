<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cart;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\MasterResource;

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

        $verificationToken = Str::random(6);

        // Buat pengguna baru
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request['password']),
            'role' => 'customer',
            'verification_code' => $verificationToken,
        ]);

        if ($user->role === 'customer') {
            Cart::create(['user_id' => $user->id]); 
            Like::create(['user_id' => $user->id]);
        }

        // kirim email verifikasi
        Mail::send('emails.verification', ['token' => $verificationToken, 'username' => $user->username], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification');
        });
    
        // Mengembalikan respons
        return new MasterResource(true, 'Registrasi Anda berhasil. Silahkan check email anda untuk mendapatkan kode verifikasi!', $user);   //memunculkan data dengan bantuan MasterResource
    }

    // verify
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:6',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        if ($user->verification_code !== $request->verification_code) {
            return response()->json(['message' => 'Invalid verification token.'], 400);
        }
    
        // Set user as verified
        $user->is_verified = true;
        $user->verification_code = null; // Clear the verification token
        $user->save();
    
        return response()->json(['message' => 'Email verified successfully.']);
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
