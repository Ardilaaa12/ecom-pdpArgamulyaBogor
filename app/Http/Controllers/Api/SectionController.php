<?php

namespace App\Http\Controllers\Api;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SectionController
{
    public function index()
    {
        $data = Section::latest()->paginate(5);
    
        return new MasterResource(true, 'List Data Dalam Section!', $data);
    }

    public function store(Request $request)
    {
        //inisialisasi
        $validator = Validator::make($request->all(), [
            'navbar_id'     => 'required|exists:navbars,id',
            'title'         => 'required',
            'description'   => 'required',
            'media'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'        => 'required|in:active,nonActive',
            'type'          => 'required',
        ]);

        // validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $image = $request->file('media');
        $imageName = $image->hashName(); // Generate nama file unik
        $image->storeAs('public/section', $imageName);

        // Generate URL untuk gambar menggunakan Storage::url()
        $imageUrl = asset('/storage/section/' . $imageName); // URL yang benar

        // tambah data
        $data = Section::create([
            'navbar_id'     => $request->navbar_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'media'         => $imageUrl,
            'status'        => $request->status,
            'type'          => $request->type,
        ]);

        return new MasterResource(true, 'Section berhasil ditambahkan!', $data);

    }

    public function show($id)
    {
        // cari ID
        $id = Section::find($id);

        // ngembaliin data
        return new MasterResource(true, 'Detail Section', $id);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'navbar_id'     => 'required|exists:navbars,id',
            'title'         => 'required',
            'description'   => 'required',
            'status'        => 'required|in:active,nonActive',
            'type'          => 'required',
        ]);

        //jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // cari data yang sesuai
        $data = Section::find($id);

        // cek media diisi atau tidak
        if ($request->hasFile('media')) {
            // Upload image
            $image = $request->file('media');
            $imageName = $image->hashName(); // Generate nama file unik
            $image->storeAs('public/section', $imageName);

            // Generate URL untuk gambar menggunakan Storage::url()
            $imageUrl = asset('/storage/section/' . $imageName); // URL yang benar

            // hapus media sebelumnya
            Storage::delete('public/section/'.basename($data->media));

            $data->update([
                'navbar_id'         => $request->navbar_id,
                'title'             => $request->title,
                'description'       => $request->description,
                'media'             => $imageUrl,
                'status'            => $request->status,
                'type'              => $request->type,

            ]);
        } else {
            $data->update([
                'navbar_id'     => $request->navbar_id,
                'title'          => $request->title,
                'description'    => $request->description,
                'status'         => $request->status,
                'type'           => $request->type,
            ]);
        }

        // mengembalikan data
        return new MasterResource(true, 'Section Berhasil Diubah!', $data);
    }
    public function destroy($id)
    {
        $data = Section::find($id);
        Storage::delete('public/section/'.basename($data->media));
        $data->delete();

        return new MasterResource(true, 'Section Behasil Dihapus!', null);
    }
}
