<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ResetCodePassword;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Erro de validação.']);
        }

        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        if ($passwordReset->created_at > now()->addMinutes(20)) {
            $passwordReset->delete();
            return response()->json(['error' => 'Código inválido ou expirado.']);
        }


        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);
        if (!isset($user)) {
            return response()->json(['error' => 'Usuário não encontrado.']);
        }
        $user->password = bcrypt($request->password);
        $user->save();

        // delete current code
        ResetCodePassword::where('email', $request->email)->delete();

        return response()->json(['message' => 'A senha foi redefinida com sucesso'], 200);
    }

    public function CodeCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Código Inválido.']);
        }

        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        if ($passwordReset->created_at > now()->addMinutes(20)) {
            $passwordReset->delete();
            return response()->json(['error' => 'O código expirou. Tente novamente.']);
        }
        return response()->json([
            'message' => "Código validado",
            'code' => $passwordReset->code],
            200);
    }

    protected function rules()
    {
        return [
            'code' => 'required|string|exists:reset_code_passwords',
            'email' => ['required', 'email', 'exists:reset_code_passwords'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

}
