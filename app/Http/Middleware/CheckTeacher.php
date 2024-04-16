<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckTeacher
{
    public function handle(Request $request, Closure $next)
    {

     $check = JWTAuth::parseToken()->authenticate();
        if (!$check) return response()->json(['message' => 'You are unauthorize for this route'],403);
            $request->id = $check->teacher_id;
            return $next($request);
    }
}
