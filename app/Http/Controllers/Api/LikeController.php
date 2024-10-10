<?php

namespace App\Http\Controllers\Api;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mengambil ID pengguna yang sedang login
        $userId = Auth::id();

        // Mengambil data like yang terkait dengan pengguna yang sedang login
        $like = Like::where('user_id', $userId) // Filter berdasarkan user_id
                    ->get();

        return new MasterResource(true, 'List like berhasil ditampilkan', $like);
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
        //
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
        $like = Like::find($id);

        $userUsingLike = $like->user()->exists();

        if($userUsingLike) {
            return redirect()->back()->with('gagal', 'like masih digunakan oleh user!');
        }

        $like->delete();

        return new MasterResource(true, 'Data cart berhasil dihapus', null);
    }
}
