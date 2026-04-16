<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStoreSession
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
        if (!$request->session()->exists('store')) {
            abort(403, 'Você precisar ter acesso a loja para acessar essa funcionalidade');
        }

        return $next($request);
    }
}
