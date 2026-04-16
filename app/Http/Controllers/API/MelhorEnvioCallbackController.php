<?php

namespace App\Http\Controllers\API;

use App\Models\Setting;
use App\Models\Settings;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MelhorEnvioCallbackController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->code) {
            return view('auth.error');
        }

        $store = Store::where('app_token', $request->app)->firstOrFail();

        $settings = Setting::where('store_id', $store->id)->first();

        if (!$settings) {
            return view('auth.error');
        }

        $isSandbox = boolval($settings->freight_info['sandbox']);
        $url = config('laravelmelhorenvio.host.production');

        if ($isSandbox) {
            $url = config('laravelmelhorenvio.host.sandbox');
        }

        $data = [
            "grant_type" => "authorization_code",
            "client_id" => $settings->freight_info['client_id'],
            "client_secret" => $settings->freight_info['client_secret'],
            "redirect_uri" => route('auth.melhor-envio') . "?app=" . $request->app,
            "code" => $request->code
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => $settings->name . " (hd@dixbpo.com)"
        ])->post(
            "{$url}/oauth/token",
            $data
        );

        if ($response->failed()) {
            Log::debug(json_encode($response->body()));
            return view('auth.error');
        }

        $freight_info = $settings->freight_info;

        $expirationDate = $response['expires_in'];

        $freight_info['token'] = $response['access_token'];
        $freight_info['refresh_token'] = $response['refresh_token'];
        $freight_info['token_expiration_date'] = Carbon::now()->addSeconds($expirationDate)->format('Y-m-d');

        $settings->freight_info = $freight_info;
        $settings->save();

        return view('auth.success');
    }
}
