<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicKeyController extends BaseController
{
    public function __invoke(Request $request)
    {
        $publicKey = null;

        $settings = Setting::where('store_id', $request->get('store')['id'])
            ->with('city.state')
            ->first();

        if ($settings && $settings->payment_info && array_key_exists('public_key', $settings->payment_info)) {
            $publicKey = $settings->payment_info['public_key'];
        }

        return $this->sendResponse($publicKey);
    }
}
