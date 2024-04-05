<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class checkadminLogin
{
    public function handle(Request $request, Closure $next)
    {

        $check = JWTAuth::parseToken()->authenticate();
        if ($check) {
            return $next($request);
        } else {
            return redirect('/api/admin/login');
        }

    }
}
