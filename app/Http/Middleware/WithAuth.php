<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class WithAuth
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
        $token = $request->header('Authorization');
        $dapp = $request->header('d-app-authorization');

        if($token == null || $dapp == null) {
           return response()->json([
                "status" => false,
                "message" => "Token not provided",
                "data" => []
            ], 401); 
        }

        try {
            if(decrypt($token) != $dapp) {
                return response()->json([
                    "status" => false,
                    "message" => "You are not login",
                    "data" => []
                ], 401);
            }
        } catch (\Throwable $th) {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid Token",
                    "data" => []
                ], 401);
        }

    
        return $next($request);
    }
}
