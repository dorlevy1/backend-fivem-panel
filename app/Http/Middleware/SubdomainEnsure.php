<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Dotenv\Dotenv;

class SubdomainEnsure
{

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $ex = explode('.', $_SERVER['HTTP_HOST']);
        $subdomain = count($ex) == 2 ? '' : '.' . $ex[0];
        if (file_exists(dirname(__DIR__) . '/../../.env' . $subdomain)) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__) . '/../../', '.env' . $subdomain);
            $dotenv->load();
        } else {
            return \response()->json([
                'success' => false,
                'message' => 'Do not init any data or cred'
            ]);

        }

        return $next($request);
    }
}
