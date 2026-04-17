<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Resolve o contexto de loja para rotas autenticadas (Sanctum) sem obrigar o header «app».
 * - Se o header «app» for enviado e for válido e o utilizador pertencer a essa loja, usa essa loja.
 * - Caso contrário, usa a primeira loja associada ao utilizador (ordenada por id).
 * Replica o formato de {@see CheckAppHeader} para os controladores que usam $request->get('store').
 */
class BindAuthenticatedUserStore
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $user->loadMissing('stores');
        $storeIds = $user->stores->pluck('id');
        if ($storeIds->isEmpty()) {
            return response()->json([
                'message' => 'Utilizador sem loja associada.',
            ], 403);
        }

        $store = null;
        $headerApp = $request->headers->get('app');
        if ($headerApp) {
            $byToken = Store::person()->where('app_token', $headerApp)->first();
            if ($byToken && $storeIds->contains($byToken->id)) {
                $store = $byToken;
            }
        }

        if (! $store) {
            $store = Store::person()
                ->whereIn('stores.id', $storeIds)
                ->orderBy('stores.id')
                ->first();
        }

        if (! $store) {
            return response()->json([
                'message' => 'Não foi possível resolver a loja do utilizador.',
            ], 500);
        }

        $storeArr = $store->toArray();
        $request->attributes->add(['store' => $storeArr]);
        $request->merge(['store' => $storeArr]);

        return $next($request);
    }
}
