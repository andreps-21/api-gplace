<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\DevAdminPassword;
use App\Support\LoginPasswordNormalizer;
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
        $emailTrim = trim((string) $request->input($field, ''));
        $user = $emailTrim === ''
            ? null
            : (User::query()->where($field, $emailTrim)->first()
                ?? User::query()->whereRaw('TRIM(email) = ?', [$emailTrim])->first());

        if ($user) {
            DevAdminPassword::syncStoredHashIfStale($user);
            $user->refresh();
        }

        $base = $this->credentials($request);
        $remember = $request->filled('remember');

        foreach (LoginPasswordNormalizer::candidates(trim((string) $request->input('password', ''))) as $candidate) {
            $base['password'] = $candidate;
            if ($this->guard()->attempt($base, $remember)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function credentials(Request $request)
    {
        $c = parent::credentials($request);
        $field = $this->username();
        if (isset($c[$field]) && is_string($c[$field])) {
            $c[$field] = trim($c[$field]);
        }

        return $c;
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
