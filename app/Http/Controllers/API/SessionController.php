<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

            if(!$user->is_enabled){
                return $this->sendError('Usuário bloqueado, favor contactar a loja.', [], 401);
            }

            $store = $request->get('store')['id'];

            if (!$user->stores->contains($store)) {
                return $this->sendError('Usuário não cadastrado nessa loja.', [], 401);
            }

            $token = $user->createToken(config('app.key'));

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
