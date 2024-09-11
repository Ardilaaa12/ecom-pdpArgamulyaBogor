<?php

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function index()
    {
        $data = Content::latest()->paginate(5);
    
        return new MasterResource(true, 'List Data Dalam Section!', $data);
    }

    public function store(Request $request)
    {
        // cek data
        $validator = Validator::make($request->all(), [
            'section_id'     => 'required|exists:sections,id',
            'title'         => 'required',
            'description'   => 'required',
            'media'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'        => 'required|in:active,nonActive',
            'type'          => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $image = $request->file('media');
        $imageName = $image->hashName(); // Generate nama file unik
        $image->storeAs('public/content', $imageName);

        // Generate URL untuk gambar menggunakan Storage::url()
        $imageUrl = asset('/storage/content/' . $imageName); // URL yang benar

        $data = Content::create([
            'section_id'    => $request->section_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'media'         => $imageUrl,
            'status'        => $request->status,
            'type'          => $request->type,
        ]);

        return new MasterResource(true, 'Content berhasil ditambahkan!', $data);
    }

    public function show($id)
    {
        $id = Content::find($id);
        return new MasterResource(true, 'Detail Content', $id);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'section_id'    => 'required|exists:sections,id',
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
        $data = Content::find($id);

        // cek media diisi atau tidak
        if ($request->hasFile('media')) {
            // Upload image
            $image = $request->file('media');
            $imageName = $image->hashName(); // Generate nama file unik
            $image->storeAs('public/content', $imageName);

            // Generate URL untuk gambar menggunakan Storage::url()
            $imageUrl = asset('/storage/content/' . $imageName); // URL yang benar

            // hapus media sebelumnya
            Storage::delete('public/content/'.basename($data->media));

            $data->update([
                'section_id'        => $request->section_id,
                'title'             => $request->title,
                'description'       => $request->description,
                'media'             => $imageUrl,
                'status'            => $request->status,
                'type'              => $request->type,

            ]);
        } else {
            $data->update([
                'section_id'     => $request->section_id,
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
        $data = Content::find($id);
        Storage::delete('public/content/'.basename($data->media));
        $data->delete();

        return new MasterResource(true, 'Content berhasil Dihapus!', null);
    }
}
