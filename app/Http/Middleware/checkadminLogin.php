<?php

namespace App\Http\Middleware;

use Closure;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class checkadminLogin
{
    public function handle(Request $request, Closure $next)
    {
        // if (session()->has('login') && session()->get('login') == true) {
        //     return $next($request);
        // $check = JWTAuth::parseToken()->authenticate();
        $check = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9 . eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hZG1pblwvc3VibWl0LWxvZ2luIiwiaWF0IjoxNzEyMjQ1Njk2LCJleHAiOjE3MTIyNDkyOTYsIm5iZiI6MTcxMjI0NTY5NiwianRpIjoiZVp0dXlnZmRWZkxGUnBGcyIsInN1YiI6MywicHJ2IjoiZWYyMjhiMTg2Mjc5MmI1NmE3NWU4NDZhYWJhYjJlM2MwZDFlNDE4OSJ9 . j8raNqFpvQpAgjESXHqIhyCTO3RVQ72qewnmWoezY4k';
        // dd($check);
        if ($check) {
            return $next($request);
        } else {
            return redirect('/api/admin/login');
        }
    }
}
