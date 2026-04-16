<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


        if (Auth::attempt(array('email' => $inputs['email'], 'password' => $inputs['password']))) {
            $id = Auth::id();

            $user = User::with('people.city.state', 'stores')->find($id);

            $user->loadMissing(['roles.permissions', 'permissions']);
            $user->setAttribute(
                'permissions',
                $user->getAllPermissions()->pluck('name')->unique()->values()->all()
            );

            if(!$user->is_enabled){
                return $this->sendError('Usuário bloqueado, favor contactar a loja.', [], 401);
            }

            $store = $request->get('store')['id'];

            if (!$user->stores->contains($store)) {
                return $this->sendError('Usuário não cadastrado nessa loja.', [], 401);
            }

            try {
                // Nome curto do token (Passport); não usar APP_KEY como nome.
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
                'token' => $token->accessToken
            ], "Login successfully.");
        } else {
            return $this->sendError('Email ou senha inválidos', [], 401);
        }
    }

    public function destroy(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 201);
    }
}
