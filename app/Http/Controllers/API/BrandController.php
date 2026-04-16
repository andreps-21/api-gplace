<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    public function index(Request $request)
    {
        $tenant = $request->get('store')['tenant_id'];

        $brands = Brand::query()
            ->select('id', 'name', 'image')
            ->where('is_enabled', true)
            ->where('is_public', true)
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->get()
            ->append('image_url');

        return $this->sendResponse($brands);
    }
}
