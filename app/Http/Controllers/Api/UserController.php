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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // find post by ID
        $user = User::findOrFail($id);

        // ambil email lama
        $oldEmail = $user->email;

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email', //validasi apakah bentunya email
            'password' => 'required|min:8',
            'fullname' => 'nullable|string',
            'address' => 'nullable',
            'phone_number' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif|max:2048',
        ]);

        if ($request->email === $oldEmail) {

            $email = $request->email;
            
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email', // Validasi untuk email unik
            ]);
            $email = $request->email;
        }

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->hasFile('image')) {
            // upload image 
            $image = $request->file('image');
            $imageName = $image->hashName();
            $image->storeAs('public/user', $imageName);
    
            $imageUrl = asset('/storage/user/' . $imageName); // URL yang benar

            // delete old image
            Storage::delete('public/user/' .basename($user->image));

            // upload user with new image 
            $user->update([
                'username' => $request->username,
                'email' => $email,
                'password' => Hash::make($request['password']),
                'fullname' => $request->fullname,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'image' => $imageUrl,
                'role' => $user->role,
            ]);
        } else {
            // update user without image 
            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request['password']),
                'fullname' => $request->fullname,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'role' => $user->role,
            ]);
        }

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
