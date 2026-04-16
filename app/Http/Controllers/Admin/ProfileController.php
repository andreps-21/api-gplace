<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('profile.edit');
    }

    /**
     * Update the profile
     *
     * @param  \App\Http\Requests\ProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileRequest $request)
    {
        $user = User::find(auth()->id());

        $user->update($request->all());

        return back()->withStatus('Perfil atualizado com sucesso.');
    }
}
