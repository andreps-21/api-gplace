<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\DevAdminPassword;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Igual ao api-genesis: actualiza o hash do admin de dev para o mês/ano correntes antes do attempt (sem cron).
     */
    protected function attemptLogin(Request $request)
    {
        $field = $this->username();
        $user = User::query()->where($field, $request->{$field})->first();

        if ($user) {
            DevAdminPassword::syncStoredHashIfStale($user);
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->is_enabled) {
            $this->guard()->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withError('Acesso com Restrição. Entre contato com o atendimento para normalizar o acesso.');
        }

        if ($user->stores()->exists()) {
            $user->load(['stores' => function ($query) {
                $query->person();
            }]);

            $store = $user->stores->first();

            session(['stores' => $user->stores->toArray()]);
            session(['store' => $store->toArray()]);
        }


        if ($user->people->tenant()->exists()) {
            $tenant = Tenant::person()->where('person_id', $user->person_id)->first();

            session(['tenant' => $tenant->toArray()]);

            if (Hash::check($user->people->nif, $user->password)) {
                return redirect()->route('auth.change-first-password.edit');
            }
        }

        return redirect()->intended($this->redirectPath());
    }
}
