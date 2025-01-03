<?php

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        $data = Content::create([
            'section_id'    => $request->section_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'media'         => '/storage/content/' . $imageName,
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
            'section_id'    => 'nullable|exists:sections,id',
            'status'        => 'nullable|in:active,nonActive',
            'media'         => 'nullable|image|mimes:jpeg,png,jpg,svg,gif|max:2048',
        ]);

        //jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cari data yang sesuai
        $data = Content::find($id);

        // cek media diisi atau tidak
        if ($request->hasFile('media')) {
            // Upload image
            $image = $request->file('media');
            $imageName = $image->hashName(); // Generate nama file unik
            $image->storeAs('public/content', $imageName);

            // hapus media sebelumnya
            if ($data->media) {
                Storage::delete('public/content/'.basename($data->media));
            }

            $data->update([
                'section_id'        => $request->section_id ?? $data->section_id,
                'title'             => $request->title ?? $data->title,
                'description'       => $request->description ?? $data->description,
                'media'             => '/storage/content/' . $imageName,
                'status'            => $request->status ?? $data->status,
                'type'              => $request->type ?? $data->type,

            ]);
        } else {
            $data->update([
                'section_id'     => $request->section_id ?? $data->section_id,
                'title'          => $request->title ?? $data->title,
                'description'    => $request->description ?? $data->description,
                'status'         => $request->status ?? $data->status,
                'type'           => $request->type ?? $data->type,
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
