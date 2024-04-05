<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class checkadminLogin
{
    public function handle(Request $request, Closure $next)
    {
       $check = Auth::guard('admins');
        dd($check->session()->get('_token'));
        if ($check) {
            return $next($request);
        } else {
            return redirect('/admin/login');
        }

    }
}
