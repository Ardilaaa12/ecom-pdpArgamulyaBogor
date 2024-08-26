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

        // upload payment_master_image
        $paymentImage = $request->file('payment_master_image');
        $paymentImage->storeAs('public/paymentImage', $paymentImage->hashName());

        // tambah data
        $data = Rekening::create([
            'payment_method'        => $request->payment_method,
            'payment_master_image'  => $paymentImage->hashName(),
        ]);

        // mengembalikan data
        return new MasterResource(true, 'Data Rekening Master Berhasil Ditambahkan!', $data);
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
            $paymentImage = $request->file('payment_master_image');
            $paymentImage->storeAs('public/paymentImage', $paymentImage->hashName());

            // hapus paymentImage sebelumnya
            Storage::delete('public/paymentImage/'.basename($post->paymentImage));

            $post->update([
                'payment_method'        => $request->payment_method,
                'payment_master_image'  => $paymentImage->hashName(),
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
        Storage::delete('public/paymentImage/'.basename($id->paymentImage));
        $id->delete();

        return new MasterResource(true, 'Data Rekening Master Berhasil dihapus!', null);
    }
}
