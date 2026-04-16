<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangeFirtsPasswordController extends Controller
{
    public function edit()
    {
        return view('auth.change-first-password');
    }

    public function update(PasswordRequest $request)
    {
        $user = User::find(auth()->id());

        $user->update(['password' => Hash::make($request->get('password'))]);

        return redirect()->route('stores.index')
            ->withStatus('Senha alterada com sucesso. Crie sua primeira loja.');
    }
}
