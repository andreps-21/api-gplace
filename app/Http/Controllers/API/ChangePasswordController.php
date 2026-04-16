<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\CurrentPasswordCheckRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends BaseController
{
    public function __invoke(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $id =  auth()->id();

        User::where('id', $id)->update([
            'password' => Hash::make($request->get('password'))
        ]);

        return $this->sendResponse([], "Senha alterada com sucesso.");
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'old_password' => ['required', 'min:6', new CurrentPasswordCheckRule],
            'password' => ['required', 'min:6', 'confirmed', 'different:old_password'],
            'password_confirmation' => ['required', 'min:6'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
