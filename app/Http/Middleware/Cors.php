<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $http_origin = $request->server('HTTP_ORIGIN');

        if (preg_match("/https?:\/\/(localhost|console.motorgraph.local|console.motorgraph.com|api.motorgraph.local|api.motorgraph.com)/i", $http_origin)) {
            $allow_origin = $http_origin;
        } else {
            $allow_origin = "";
        }

        return $next($request)
            ->header('Access-Control-Allow-Origin', $allow_origin)
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Max-Age', '1000');
    }
}
