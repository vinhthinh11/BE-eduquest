<?php

namespace App\Http\Middleware;

use App\Models\admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::admin();
        $admin = admin::find($user->admin_id);

        $request->session()->put('user_info', [
            'admin_id'  => $admin->admin_id,
            'username'  => $admin->username,
            'name'      => $admin->name,
            'avatar'    => $admin->avatar,
        ]);

        return $next($request);
    }
}
