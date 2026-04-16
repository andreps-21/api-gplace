<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public function index(Request $request)
    {
        $data = PaymentMethod::where('is_enabled', true)
        ->whereHas('stores', fn($query) => $query->where('stores.id', $request->get('store')['id']))
        ->get();

        return $this->sendResponse($data);
    }
}
