<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class SessionController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);


        if ($validator->fails()) {
            return $this->sendError('Erro de validação', $validator->errors()->toArray(), 422);
        }

        $inputs = $request->all();

        /*
         * Não usar Auth::attempt() aqui: o guard `web` grava sessão (SessionGuard::login),
         * mas as rotas `api/*` não carregam StartSession — em produção isso provoca 500
         * ("Session store not set on request"). Validação manual + Passport token.
         */
        $user = User::where('email', $inputs['email'])->first();

        if (! $user || ! Hash::check($inputs['password'], $user->password)) {
            return $this->sendError('Email ou senha inválidos', [], 401);
        }

        $user->load(['people.city.state', 'stores']);
        $user->loadMissing(['roles.permissions', 'permissions']);

        try {
            $user->setAttribute(
                'permissions',
                $user->getAllPermissions()->pluck('name')->unique()->values()->all()
            );
        } catch (Throwable $e) {
            Log::warning('getAllPermissions falhou no login; a continuar sem lista.', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
            $user->setAttribute('permissions', []);
        }

        if (! $user->is_enabled) {
            return $this->sendError('Usuário bloqueado, favor contactar a loja.', [], 401);
        }

        // Login sem header «app»: o contexto de loja nas rotas autenticadas é resolvido por
        // BindAuthenticatedUserStore (primeira loja do utilizador ou header «app» opcional).

        try {
            $token = $user->createToken('gplace-session');
        } catch (RuntimeException $e) {
            Log::error('Passport personal access client em falta ou chaves OAuth inválidas.', [
                'exception' => $e->getMessage(),
            ]);

            return $this->sendError(
                'Autenticação por token indisponível no servidor. Executa `php artisan passport:install` (ou migrações Passport) e confirma `storage/oauth-*.key`.',
                [],
                503
            );
        } catch (Throwable $e) {
            Log::error('Erro ao emitir token Passport no login.', ['exception' => $e]);

            return $this->sendError(
                'Não foi possível concluir o login. Verifica os logs do servidor (Passport / chaves OAuth).',
                [],
                503
            );
        }

        return $this->sendResponse([
            'user' => $user,
            'token' => $token->accessToken,
        ], 'Login successfully.');
    }

    public function destroy(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 201);
    }
}
