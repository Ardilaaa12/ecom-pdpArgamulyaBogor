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

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        // upload image
        $image = $request->file('image');
        $path = $image->storeAs('public/users', $image->hashName());

        // create users
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request['password']),
            'fullname' => $request->fullname,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'image' => $image->hashName(),
            'role' => $request->role,
        ]);

        if ($user->role === 'customer') {
            Cart::create(['user_id' => $user->id]); 
            Like::create(['user_id' => $user->id]);
        }

        if($user) {
            return new MasterResource(true, 'Data user berhasil ditambahkan', $user);
        } else {
            // hapus gambar jika penyimpanan data gagal 
            Storage::delete($path);
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

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // find post by ID
        $user = User::find($id);

        // check if image is not empty 
        if($request->hasFile('image')) {
            // upload image 
            $image = $request->file('image');
            $image->storeAs('public/users', $image->hashName());

            // delete old image
            Storage::delete('public/users/' . basename($user->image));

            // upload user with new image 
            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request['password']),
                'fullname' => $request->fullname,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'image' => $image->hashName(),
                'role' => $request->role,
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
                'role' => $request->role,
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
        Storage::delete('public/users' . basename($user->image));
        // delete user
        $user->delete();

        return new MasterResource(true, 'Data user berhasil dihapus', null);
    }
}
