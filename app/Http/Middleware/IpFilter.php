<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

class IpFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        if ($request->ip() == null) {
            throw new \Exception("IP ADDRESS NOT SET");
        }

        $clientIp = $request->ip();
        $allowIps = Config::get("auth.allow.ip");

        if (!in_array($clientIp, $allowIps)) {
            throw new \Exception("Access denied!");
        }

        return $next($request);
    }
}
