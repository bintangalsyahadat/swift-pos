<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Paksa semua request ke API untuk selalu meminta JSON response.
     * Tanpa ini, Laravel akan melakukan redirect HTML ketika terjadi
     * error autentikasi — karena tidak ada header "Accept: application/json".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
