<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class logreponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        \Log::info('Incoming request form logreponse', [ 
            
            'method' => $request->method(), 
            'headers' => $request->headers->all(),
            'url' => $request->fullUrl(), 
            'ip' => $request->ip(), ]);
        return $next($request);
    }
}
