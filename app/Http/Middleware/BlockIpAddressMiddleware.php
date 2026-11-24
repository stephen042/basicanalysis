<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ipaddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BlockIpAddressMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ipaddresses = [];
        try {
            if (Schema::hasTable('ipaddresses')) {
                $ipaddresses = DB::table('ipaddresses')->pluck('ipaddress')->toArray();
            }
        } catch (\Throwable $e) {
            // A missing/unavailable blocklist must not make every route fail.
        }

        if (in_array($request->ip(), $ipaddresses, true)) {
            abort(403, 'You are restricted to access the site.');
        }

        return $next($request);
    }
}
