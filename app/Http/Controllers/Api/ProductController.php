<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
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
        $product = Product::with(['category'])->where('stock', '>', 0)->get();
        return new MasterResource(true, 'List product berhasil ditampilkan', $product);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('input category_id:', $request->all());

        // Validasi input
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'name_product' => 'required',
            'age' => 'nullable',
            'weight' => 'nullable',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'photo_product' => 'required|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
        ]);
    
        // Jika validasi gagal, kembalikan error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = Category::where('name_category', $request->category)->first();
    
        // Simpan file gambar ke dalam folder 'public/product'
        $photoProduct = $request->file('photo_product');
        $photoProductName = $photoProduct->hashName(); // Generate nama file unik
        $photoProduct->storeAs('public/product', $photoProductName);
    
        // Generate URL untuk gambar menggunakan helper asset()
        $photoProductUrl = asset('storage/product/' . $photoProductName);
    
        // Simpan data produk ke database
        $product = Product::create([
            'category_id' => $data->id,
            'name_product' => $request->name_product,
            'age' => $request->age ?? '-',
            'weight' => $request->weight ?? '-',
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'photo_product' => $photoProductUrl, // Simpan URL gambar
        ]);
    
        // Cek jika penyimpanan data berhasil
        if ($product) {
            return new MasterResource(true, 'Data product berhasil ditambahkan', $product);
        } else {
            // Hapus file gambar jika penyimpanan data gagal
            Storage::delete('public/product/' . $photoProductName);
            return response()->json(['error' => 'Gagal menyimpan data produk'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['category'])->findOrFail($id);
        return new MasterResource(true, 'Detai data product', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|numeric',
            'name_product' => 'nullable',
            'age' => 'nullable',
            'weight' => 'nullable',
            'description' => 'nullable',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|numeric',
            'photo_product' => 'nullable|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('photo_product')) {
            // upload image 
            $photoProduct = $request->file('photo_product');
            $photoProductName = $photoProduct->hashName();
            $photoProduct->storeAs('public/product', $photoProductName);
        
            $photoProductUrl = asset('storage/product/' . $photoProductName);

            // delete old image 
            Storage::delete('public/product/' . basename($product->photo_product));
            // upload product with new image 
            $product->update([
                'category_id'   => $request->category_id ?? $product->category_id,
                'name_product'  => $request->name_product ?? $product->name_product,
                'age'           => $request->age ?? $product->age,
                'weight'        => $request->weight ?? $product->weight,
                'description'   => $request->description ?? $product->description,
                'price'         => number_format($request->price) ?? $product->price,
                'stock'         => $request->stock ?? $product->stock,
                'photo_product' => $photoProductUrl,
            ]);
        } else {
            // without image
            $product->update([
                'category_id'   => $request->category_id ?? $product->category_id,
                'name_product'  => $request->name_product ?? $product->name_product,
                'age'           => $request->age ?? $product->age,
                'weight'        => $request->weight ?? $product->weight,
                'description'   => $request->description ?? $product->description,
                'price'         => number_format($request->price) ?? $product->price,
                'stock'         => $request->stock ?? $product->stock,
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
