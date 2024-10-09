<?php

namespace App\Http\Controllers\Api;

use App\Models\Rekening;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
// memeriksa data apakah yang diiput sudah sesuai
use Illuminate\Support\Facades\Validator;
// bisa menerima request yang dikirim pengguna
use Illuminate\Http\Request;
//import facades Storage
use Illuminate\Support\Facades\Storage;

class RekeningController extends Controller
{
    public function index()
    {
        $data = Rekening::latest()->paginate(5);

        // proses mengambil data dan mengembalikan data ke function
        return new MasterResource(true, 'List Data Rekening', $data);
    }

    public function store(Request $request)
    {
        //inisialisasi
        $validator = Validator::make($request->all(), [
            'payment_method'        => 'required',
            'payment_master_image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // cek apakah validasinya gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
            // 422 adalah code yang akan dimunculkan dengan keterangan errornya
        }

        $rekeningImage = $request->file('payment_master_image');
        $rekeningImageName = $rekeningImage->hashName();
        $rekeningImage->storeAs('public/rekening', $rekeningImageName);

        $rekeningImageUrl = asset('storage/rekening/' . $rekeningImageName);

        // tambah data
        $data = Rekening::create([
            'payment_method'        => $request->payment_method,
            'payment_master_image'  => $rekeningImageUrl,
        ]);

        if($data) {
            return new MasterResource(true, 'Data user berhasil ditambahkan', $data);
        } else {
         // Hapus file gambar jika penyimpanan data gagal
         Storage::delete('public/rekening/' . $rekeningImageName);
         return response()->json(['error' => 'Gagal menyimpan data payment'], 500);
        }
    }

    public function show($id)
    {
        // cari data dari ID
        $id = Rekening::find($id);

        // mengembalikan data
        return new MasterResource(true, 'Detail Data Method Payment!', $id);
    }

    public function update(Request $request, $id)
    {
        // validasi data yang diisi
        $validator = Validator::make($request->all(), [
            'payment_method'    => 'required',
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // cari data sesuai id
        $post = Rekening::find($id);

        // cek apakah paymnet image diisi atau tidak
        if ($request->hasFile('payment_master_image')) {
            //upload payment image
            $rekeningImage = $request->file('payment_master_image');
            $rekeningImageName = $rekeningImage->hashName();
            $rekeningImage->storeAs('public/rekening', $rekeningImageName);
    
            $rekeningImageUrl = asset('storage/rekening/' . $rekeningImageName);
            
            // hapus paymentImage sebelumnya
            Storage::delete('public/rekening/'.basename($post->payment_master_image));

            $post->update([
                'payment_method'        => $request->payment_method,
                'payment_master_image'  => $rekeningImageUrl,
            ]);
        } else {
            // update tanpa paymentImage
            $post->update([
                'payment_method'    => $request->payment_method,
            ]);
        }

        // mengembalikan nilai
        return new MasterResource(true, 'Data Rekening Master Berhasil Diubah!', $post);
    }

    public function destroy($id)
    {
        $id = Rekening::find($id);
        Storage::delete('public/rekening/'.basename($id->payment_master_image));
        $id->delete();

        return new MasterResource(true, 'Data Rekening Master Berhasil dihapus!', null);
    }

    public function search(Request $request) 
    {
        $query = $request->input('query');
        
        // Periksa apakah query memiliki nilai sebelum dijalankan
        if (!$query) {
            return response()->json(['message' => 'Query tidak ditemukan'], 400);
        }

        $rekening = Rekening::where('payment_method', 'LIKE', "%{$query}%")->get();

        // Jika data tidak ditemukan, beri response yang sesuai
        if ($rekening->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($rekening);
    }
}
