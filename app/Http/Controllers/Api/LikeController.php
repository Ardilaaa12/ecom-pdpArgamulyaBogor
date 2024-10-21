<?php

namespace App\Http\Controllers\Api;

use App\Models\Like;
use App\Models\LikeItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mengambil ID pengguna yang sedang login
        $userId = Auth::id();

        // Mengambil data like yang terkait dengan pengguna yang sedang login dan memuat data like_items
        $likes = Like::where('user_id', $userId)
                    ->with('likeItems') // Muat relasi like_items
                    ->get();

        return new MasterResource(true, 'List like berhasil ditampilkan', $likes);
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
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Mengambil ID pengguna yang sedang login
        $userID = Auth::user();
        $like = Like::where('user_id', $userID->id)->first();

        // Jika like tidak ditemukan untuk pengguna yang login
        if (!$like) {
            return response()->json(['error' => 'Anda bukan Customer'], 404);
        }

        // Cek apakah produk ada
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Product tidak ditemukan'], 404);
        }

        // Cari item di like berdasarkan product_id
        $likeItem = LikeItem::where('like_id', $like->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        // Jika item sudah ada dalam wishlist
        if ($likeItem) {
            return response()->json(['error' => 'Sudah dimasukan kedalam Wishlist'], 409);
        }

        // Buat item baru di like
        $likeItem = LikeItem::create([
            'like_id' => $like->id,
            'product_id' => $request->product_id,
        ]);

        return new MasterResource(true, 'Data berhasil ditambahkan kedalam Wishlist', $likeItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $likeItem = LikeItem::find($id);
        $likeItem->delete();
        return new MasterResource(true, 'Data like item berhasil di hapus', null);
    }
}
