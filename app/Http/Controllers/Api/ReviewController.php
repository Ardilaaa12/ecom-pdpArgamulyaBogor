<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MasterResource;
// untuk format waktu
use Carbon\Carbon;

class ReviewController extends Controller
{
   public function index()
   {
        $data = Review::latest()->paginate(5);

        return new MasterResource(true, 'List Data Review', $data);
   }

   public function store(Request $request)
   {
        // validasi
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'product_id'    => 'required|exists:products,id',
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description'   => 'required',
            'rate'          => 'required|integer|in:1,2,3,4,5',
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // kirim gambar
        $image = $request->file('image');
        $image->storeAs('public/reviewImage', $image->hashName());

        // tambah data
        $data = Review::create([
            'user_id'       => $request->user_id,
            'product_id'    => $request->product_id,
            'image'         => $image->hashName(),
            'description'   => $request->description,
            'review_date'   => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'), //agar format waktu sama
            'amount_like'   => $request->amount_like ?? 0,
            'rate'          => $request->rate,
        ]);

        return new MasterResource(true, 'Review Berhaisl Ditambahkan!', $data);
        
   }

   public function show($id)
   {
        // nyari data
        $id = Review::find($id);
        
        // mengembalikan data
        return new MasterResource(true, "Detail Review", $id);
   }

   public function update(Request $request, $id)
   {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'product_id'    => 'required|exists:products,id',
            'description'   => 'required',
            'rate'          => 'required|integer|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        };

        $data = Review::find($id);

        if ($request->hasFile('image')) {
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/reviewImage', $image->hashName());

            // hapus image sebelumnya
            Storage::delete('public/reviewImage/'.basename($data->image));

            // update data
            $data->update([
                'user_id'       => $request->user_id,
                'product_id'    => $request->product_id,
                'image'         => $image->hashName(),
                'description'   => $request->description,
                'rate'          => $request->rate,
            ]);
        } else {
            $data->update([
                'user_id'       => $request->user_id,
                'product_id'    => $request->product_id,
                'description'   => $request->description,
                'rate'          => $request->rate,
            ]);
        }

        return new MasterResource(true, 'Review Berhasil Diubah!', $data);
   }

   public function destroy($id)
   {
        $data = Review::find($id);
        Storage::delete('public/reviewImage/'.basename($data->image));
        $data->delete();

        return new MasterResource(true, 'Review Berhasil dihapus!', null);
   }
}