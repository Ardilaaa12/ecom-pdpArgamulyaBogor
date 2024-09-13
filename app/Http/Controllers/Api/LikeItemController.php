<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use App\Models\LikeItem;
use Illuminate\Http\Request;
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
            'like_id' => 'required|exists:likes,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $likeItem = LikeItem::create([
            'like_id' => $request->like_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Berhasil menambahkan item ke dalam like item', $likeItem);
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
