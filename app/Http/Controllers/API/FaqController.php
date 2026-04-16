<?php

namespace App\Http\Controllers\API;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends BaseController
{

    public function index(Request $request)
    {
        $data =  Faq::where('is_enabled', true)
        ->where('store_id', $request->get('store')['id'])
        ->orderBy('created_at', 'desc')
        ->get();

        return $this->sendResponse($data);
    }


    public function show(Request $request, $id)
    {
        $item = Faq::query()
            ->where('id', $id)
            ->where('is_enabled', true)
            ->where('store_id', $request->get('store')['id'])
            ->firstOrFail();

        return $this->sendResponse($item);
    }
}
