<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\Token;
use Closure;
use Illuminate\Http\Request;

class ValidatedToken
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
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token não informada.'
            ], 401);
        }

        if (!$request->headers->has('app')) {
            return response()->json([
                'message' => 'App ID não informada.'
            ], 401);
        }

        $store = Store::person()
            ->where('app_token', $request->header('app'))
            ->first();

        if (!$store) {
            return response()->json([
                'message' => 'App ID não válida.'
            ], 401);
        }

        $isValidToken = Token::where('access_token', $token)
            ->where('store_id', $store->id)
            ->exists();

        if (!$isValidToken) {
            return response()->json([
                'message' => 'Token inválido.'
            ], 401);
        }

        $request->attributes->add(['store' => $store->toArray()]);

        return $next($request);
    }
}
