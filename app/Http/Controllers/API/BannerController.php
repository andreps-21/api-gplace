<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query =  Banner::query()
            ->where('store_id', $request->get('store')['id'])
            ->where('banners.is_enabled', true)
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('banners.type', $request->type);
            })
            ->with('sizeImages.pivot.interfacePosition')
            ->orderBy("sequence");

        ($request->has('page'))  ? $data = $query->paginate(10) : $data = $query->get();

        return $this->sendResponse($data);
    }
}
