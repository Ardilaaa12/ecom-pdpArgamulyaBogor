<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use App\Models\Category;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::with(['category'])->get();
        return new MasterResource(true, 'List product berhasil ditampilkan', $product);
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
            'category_id' => 'required',
            'name_product' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'photo_product' => 'required|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
        ]);
    
        // Jika validasi gagal, kembalikan error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Simpan file gambar
        $photoProduct = $request->file('photo_product');
        $photoProductName = $photoProduct->hashName();
        $path = $photoProduct->storeAs('public/product', $photoProductName);
    
        // Simpan data produk ke database
        $product = Product::create([
            'category_id' => $request->category_id,
            'name_product' => $request->name_product,
            'description' => $request->description,
            'price' => number_format($request->price),
            'stock' => $request->stock,
            'photo_product' => $photoProductName,
        ]);
    
        // Cek jika penyimpanan data berhasil
        if ($product) {
            return new MasterResource(true, 'Data product berhasil ditambahkan', $product);
        } else {
            // Hapus file gambar jika penyimpanan data gagal
            Storage::delete($path);
            return response()->json(['error' => 'Gagal menyimpan data produk'], 500);
        }
    }    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id)->with(['category'])->get();
        return new MasterResource(true, 'Detai data product', $product);
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
            'category_id' => 'required',
            'name_product' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'photo_product' => 'required|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);

        if ($request->hasFile('photo_product')) {
            // upload image 
            $photoProduct = $request->file('photo_product');
            $photoProduct->storeAs('public/product', $photoProduct->hashName());
            // delete old image 
            Storage::delete('public/product/' . basename($product->photo_product));
            // upload product with new image 
            $product->update([
                'category_id' => $request->category_id,
                'name_product' => $request->name_product,
                'description' => $request->description,
                'price' => number_format($request->price),
                'stock' => $request->stock,
                'photo_product' => $photoProduct->hashName(),
            ]);
        } else {
            // without image
            $product->update([
            'category_id' => $request->category_id,
            'name_product' => $request->name_product,
            'description' => $request->description,
            'price' => number_format($request->price),
            'stock' => $request->stock,
            ]);
        }

        return new MasterResource(true, 'Data product berhasil diubah', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::with(['category'])->find($id);
        Storage::delete('public/product' . basename($product->photo_product));
        $product->delete();

        return new MasterResource(true, 'Data user berhasil dihapus', null);
    }
}
