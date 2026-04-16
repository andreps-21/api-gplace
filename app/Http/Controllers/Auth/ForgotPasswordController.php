<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Não conseguimos encontrar um usuário com esse endereço de e-mail.']);
        }

        ResetCodePassword::where('email', $request->email)->delete();

        // Generate random code
        $data['email'] = $request->email;
        $data['code'] = mt_rand(100000, 999999);
        $data['created_at'] = now();

        // Create a new code
        $codeData = ResetCodePassword::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return redirect()->route('password.reset-code')->with('status', __('passwords.sent'));
    }
}
