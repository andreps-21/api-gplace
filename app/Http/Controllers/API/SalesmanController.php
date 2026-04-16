<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use Illuminate\Http\Request;

class SalesmanController extends BaseController
{
    public function index(Request $request)
    {
        $data = Salesman::person()
            ->whereHas('stores', function ($que) use($request) {
                return $que->where('store_id', $request->get('store')['id']);
            })
            ->orderBy('name')
            ->get(10);

        return $this->sendResponse($data);
    }
}
