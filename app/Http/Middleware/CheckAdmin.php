<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {

        $check = JWTAuth::parseToken()->authenticate();
        if ($check->permission == 1) {
            $request->id = $check->admin_id;
            return $next($request);
        } else {
            return response()->json(['message' => 'You are unauthorize for this route'],403);
        }

    }
}
