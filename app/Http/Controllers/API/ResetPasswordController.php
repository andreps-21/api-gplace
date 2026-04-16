<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ResetCodePassword;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends BaseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
            'email' => ['required', 'email', 'exists:reset_code_passwords'],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação', $validator->errors()->all(), 422);
        }

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        if ($passwordReset->created_at > now()->addMinutes(20)) {
            $passwordReset->delete();
            return $this->sendError("O código expirou. Tente novamente.", [], 422);
        }

        if ($passwordReset->email != $request->email) {
            return $this->sendError("O email não confere com o código.", [], 422);
        }

        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);

        $user->password = bcrypt($request->password);
        $user->save();

        // delete current code
        ResetCodePassword::where('email', $request->email)->delete();

        return $this->sendResponse([], 'A senha foi redefinida com sucesso');
    }
}
