<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     // Cek apakah pengguna yang sedang login memiliki role 'customer'
    //     if (Auth::check() && Auth::user()->role === 'customer') {
    //         return $next($request);
    //     }

    //     // Jika tidak, kembalikan respons JSON dengan pesan error
    //     return response()->json([
    //         'message' => 'Oops! You do not have permission to access this resource.'
    //     ], 403); // 403 Forbidden
    // }
}
