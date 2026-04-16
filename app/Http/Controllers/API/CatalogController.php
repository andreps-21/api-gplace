<?php

namespace App\Http\Controllers\API;

use App\Models\Catalog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CatalogController extends BaseController
{

    public function index(Request $request)
    {
        $data =  Catalog::where('is_enabled', true)->where('store_id', $request->get('store')['id'])->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($data);
    }


    public function show(Request $request, $id)
    {
        $item = Catalog::query()
            ->where('id', $id)
            ->where('store_id', $request->get('store')['id'])
            ->where('is_enabled', true)
            ->firstOrFail();

        return $this->sendResponse($item);
    }
}
