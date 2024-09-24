<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah pengguna sedang login
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401); // 401 Unauthorized
        }

        // Cek role pengguna
        $userRole = Auth::user()->role;

        // Tampilkan role pengguna untuk debugging
        // Anda dapat menghapus baris ini setelah selesai debugging
        // dd($userRole);

        // Cek apakah role pengguna adalah 'customer'
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Jika tidak, kembalikan respons JSON dengan pesan error
        return response()->json([
            'message' => 'Oops! You do not have permission to access this resource.'
        ], 403); // 403 Forbidden
    }
}
