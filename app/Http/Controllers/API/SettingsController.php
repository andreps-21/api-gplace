<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends BaseController
{
    public function index(Request $request)
    {
        $settings = Setting::where('store_id', $request->get('store')['id'])
        ->with('city.state', 'socialMedias')
            ->first();

        if ($settings) {
            $settings->append('logo_url');
        }

        $stamps = [];
        if ($settings) {
            if (!empty($settings->stamps)) {
                $stamps = collect(json_decode($settings->stamps));
                $settings->stamps = $stamps;
            }
        }

        unset($settings->payment_info);
        unset($settings->freight_info);


        return $this->sendResponse($settings);
    }
}
