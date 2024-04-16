<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class CheckStudent
{
    public function handle(Request $request, Closure $next)
    {

     $check = JWTAuth::parseToken();
     return response()->json($check);
        // if (!$check) return response()->json(['message' => 'You are unauthorize for this route'],403);
        // $request->id = $check->student_id;
        // return $next($request);
    }
}
