<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItem = CartItem::with(['cart'], ['product'])->get();
        return New MasterResource(true, 'List data yang terdapat di cart item', $cartItem);
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
        $cart = Cart::where('user_id', $userID->id)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart tidak ditemukan'], 404);
        }

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data berhasil di tambahkan kedalam cart', $cartItem);
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

        $cartItem = CartItem::find($id);
        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data cart item berhasil di update', $cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartItem = CartItem::find($id);

        $cartItem->delete();
        return new MasterResource(true, 'Data yang berada di cart item berhasil di hapus', null);
    }
}
