<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ResetCodePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodeCheckController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação', $validator->errors()->all(), 422);
        }

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addMinutes(20)) {
            $passwordReset->delete();
            return $this->sendError("O código expirou. Tente novamente.", [], 422);
        }

        return $this->sendResponse([
            'code' => $passwordReset->code
        ], "Código validado");
    }
}
