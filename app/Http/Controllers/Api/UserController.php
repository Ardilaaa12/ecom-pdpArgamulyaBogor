<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $data = User::latest()->paginate(5);

        return new MasterResource(true, 'List Data Resource', $data);
    }

    public function store(Request $request)
    {
        // validasi data
        $validator = Validator::make($request->all(), [
            'username'  => 'required',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'fullname'  => 'required|string',
            'address'   => 'required',
            'phone_number'    => 'required|numeric',
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'userRole'  => 'required|in:admin,customer',
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload imageUser
        $imageUser = $request->file('image');
        $imageUser->storeAs('public/imageUser', $imageUser->hashName());

        // bentuk password diubah
        $pass = Hash::make($request->password);
        // tambah data
        $data = User::create([
            'username'          => $request->username,
            'email'             => $request->email,
            'password'          => $pass,
            'fullname'          => $request->fullname,
            'address'           => $request->address,
            'phone_number'      => $request->phone_number,
            'image'             => $imageUser->hashName(),
            'userRole'          => $request->userRole,
        ]);

        // mengembalikan data
        return new MasterResource(true, 'Data User Berhasil Ditambahkan!', $data);
    }
}
