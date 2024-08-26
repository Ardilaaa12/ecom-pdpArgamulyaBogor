<?php

namespace App\Http\Controllers\Api;

use App\Models\Navbar;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NavbarController extends Controller
{
    public function index()
    {
        $data = Navbar::where('status', 'active')->latest()->get();

        return new MasterResource(true, 'List Navbar yang Aktif', $data);
    }

    public function store(Request $request)
    {
        // inisalisasi
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'route'     => 'required',
            'status'    => 'required|in:active,nonActive',
            'type'      => 'required',
        ]);

        // validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->error(), 422);
        }

        $data = Navbar::create([
            'name'      => $request->name,
            'route'     => $request->route,
            'status'    => $request->status,
            'type'      => $request->type,
        ]);

        // mengembalikan data
        return new MasterResource(true, 'Navbar Telah Ditambahkan!', $data);
    }

    public function show($id)
    {
        //cari id yang dicari
        $id = Navbar::find($id);

        // mengembalikan nilai
        return new MasterResource(true, 'Detail Navbar', $id);
    }

    public function update(Request $request, $id)
    {
        // validasi data yang diisi
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'route'     => 'required',
            'status'    => 'required|in:active,nonActive',
            'type'      => 'required',
        ]);

        // validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->error(), 422);
        }

        // cari data yang diubah
        $id = Navbar::find($id);

        // update
        $id->update([
            'name'      => $request->name,
            'route'     => $request->route,
            'status'    => $request->status,
            'type'      => $request->type,
        ]);

        // mengembalikan nilai
        return new MasterResource(true, 'Navbar Berhasil Diubah!', $id);
    }

    public function destroy($id){
        $id = Navbar::find($id);
        $id->delete();

        $data = Navbar::latest()->get();

        return new MasterResource(true, 'Navbar Berhasil Dihapus!', $data);
    }
}