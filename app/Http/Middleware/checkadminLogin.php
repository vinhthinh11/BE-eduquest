<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkadminLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('login') && session()->get('login') == true) {
            return $next($request);
        } else {
            return redirect('/admin/login');
        }

    }
}
