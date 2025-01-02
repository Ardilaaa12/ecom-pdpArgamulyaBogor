<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriController extends Controller
{
    public function index()
    {
        $category = Category::latest()->get();
        return new MasterResource(true, 'List category berhasil ditampilkan', $category);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_category' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = Category::create([
            'name_category' => $request->name_category,
        ]);

        return new MasterResource(true, 'Data category berhasil ditambahkan', $category);
    }

    public function show(string $id)
    {
        $category = Category::find($id);
        return new MasterResource(true, 'Detail data post', $category);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_category',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = Category::find($id);
        $category->update([
            'name_category' => $request->name_category,
        ]);

        return new MasterResource(true, 'Data category berhasil diubah', $category);
    }   

    public function destroy(string $id)
    {
        $category = Category::find($id);

        if ($category->product()->exists()) {
            return new MasterResource(false, 'Category memiliki relasi dengan table produk', null);
        }

        // delete post
        $category->delete();
        return new MasterResource(true, 'Data berhasil dihapus', $category);
    }
}
