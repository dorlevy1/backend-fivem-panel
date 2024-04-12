<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $permissions = explode(',', json_decode(Auth::user()->permissions)->scopes);
        if ( !in_array('staff', $permissions, true)) {
            return redirect('');
        }

        return $next($request);
    }
}
