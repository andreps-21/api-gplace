<?php

namespace App\Http\Controllers\API;

use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends BaseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação', $validator->errors()->all(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('Email não cadastrado.', [], 422);
        }

        // Delete all old code that user send before.
        ResetCodePassword::where('email', $request->email)->delete();

        // Generate random code
        $data['email'] = $request->email;
        $data['code'] = mt_rand(100000, 999999);
        $data['created_at'] = now();

        // Create a new code
        $codeData = ResetCodePassword::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return $this->sendResponse([], "O link de resetar senha foi enviado no seu email.");
    }

}
