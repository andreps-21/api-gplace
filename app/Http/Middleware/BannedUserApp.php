<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BannedUserApp
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
        if (
            auth()->check() &&
            !auth()->user()->roles()->exists()
        ) {
            auth()->logout();

            $message = 'O usuário não tem acesso ao backoffice. Contate o administrador.';

            return redirect()->route('login')->withError($message);
        }

        return $next($request);

    }
}
