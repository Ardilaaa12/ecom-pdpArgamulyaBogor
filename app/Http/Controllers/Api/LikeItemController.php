<?php

namespace App\Http\Controllers\Api;

use App\Models\Like;
use App\Models\LikeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class LikeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $likeItem = LikeItem::with(['like'], ['product'])->get();
        return new MasterResource(true, 'List data yang ada di like item', $likeItem);
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userID = Auth::user();
        $like = Like::where('user_id', $userID->id)->first();

        if (!$like) {
            return response()->json(['error' => 'Like tidak ditemukan'], 404);
        }

        $likeItem = LikeItem::create([
            'like_id' => $like->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data berhasil di tambahkan kedalam cart', $likeItem);
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
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $likeItem = LikeItem::find($id);
        $likeItem->update([
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data like item berhasil di ubah', $likeItem);
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
