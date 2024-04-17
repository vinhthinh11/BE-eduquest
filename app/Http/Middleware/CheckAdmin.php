<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
    if(!$request->user('admins')){
            return response()->json(['message' => 'You are unauthorize for this route'],403);
    }
    $request->id = $request->user('admins')->admin_id;
        return $next($request);
    }
}
