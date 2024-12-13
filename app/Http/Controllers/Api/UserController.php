<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() 
    {
        $user = User::latest()->get();
        return new MasterResource(true, 'List user berhasil ditampilkan', $user);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'fullname' => 'required|string',
            'address' => 'required',
            'phone_number' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg,gif|max:2048',
            'role' => 'required|in:admin,customer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $image = $request->file('image');
        $imageName = $image->hashName(); // Generate nama file unik
        $image->storeAs('public/user', $imageName);

        // Generate URL untuk gambar menggunakan Storage::url()
        $imageUrl = asset('/storage/user/' . $imageName); // URL yang benar

        // Create user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'fullname' => $request->fullname,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'image' => $imageUrl, // Simpan URL gambar
            'role' => $request->role,
        ]);

        if ($user->role === 'customer') {
            Cart::create(['user_id' => $user->id]); 
            Like::create(['user_id' => $user->id]);
        }

        if ($user) {
            return new MasterResource(true, 'Data user berhasil ditambahkan', $user);
        } else {
            // Hapus gambar jika penyimpanan data gagal
            Storage::delete('public/user/' . $imageName);
            return response()->json(['error' => 'Gagal menyimpan data user'], 500);
        }
    } 
    

    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        return new MasterResource(true, 'Detail data user', $user);
    }

    // customer
    public function getUser(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    // Admin & Customer
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $oldEmail = $user->email;

        // Tambahkan log untuk memeriksa input
        // \Log::info('Input image:', $request->all());

        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'nullable',
            'email' => 'nullable|email|unique:users,email,' . $id, // Validasi unik kecuali untuk user yang sedang diperbarui
            'password' => 'nullable|min:8',
            'fullname' => 'nullable|string',
            'address' => 'nullable',
            'phone_number' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Mengisi data yang akan diupdate, tetap menggunakan nilai lama jika tidak diisi di request
        $dataToUpdate = [
            'username' => $request->username ?? $user->username,
            'fullname' => $request->fullname ?? $user->fullname,
            'address' => $request->address ?? $user->address,
            'phone_number' => $request->phone_number ?? $user->phone_number,
            'role' => $user->role,
        ];

        // Cek jika email diubah
        if ($request->email && $request->email !== $oldEmail) {
            $dataToUpdate['email'] = $request->email;
        }

        // Cek jika password diubah
        if ($request->password) {
            $dataToUpdate['password'] = Hash::make($request['password']);
        }

        // Cek jika ada file gambar
        if ($request->hasFile('image')) {
            // Upload image 
            $image = $request->file('image');
            $imageName = $image->hashName();
            $image->storeAs('public/user', $imageName);
            $imageUrl = asset('/storage/user/' . $imageName);

            // Hapus gambar lama
            if ($user->image) {
                Storage::delete('public/user/' . basename($user->image));
            }

            $dataToUpdate['image'] = $imageUrl;
        }

        // Update user dengan data yang sudah disiapkan
        $user->update($dataToUpdate);
        
        return new MasterResource(true, 'Data user berhasil diubah!', $user);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        // delete image
        Storage::delete('public/user/' .basename($user->image));
        // delete user
        $user->delete();

        return new MasterResource(true, 'Data user berhasil dihapus', null);
    }
}
