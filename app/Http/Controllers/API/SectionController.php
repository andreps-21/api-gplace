<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends BaseController
{
    public function index(Request $request)
    {
        $store = $request->get('store')['id'];


        $sections = Section::query()
            ->where('is_enabled', true)
            ->where('store_id', $store)
            ->orderBy('order')
            ->where(function ($query) {
                $query->whereHas('products', fn ($query) => $query->where('is_enabled', true))
                    ->orWhereHas('children', function ($q) {
                        $q->whereHas('products', fn ($query) => $query->where('is_enabled', true))
                            ->orWhereHas('auxProducts', fn ($query) => $query->where('is_enabled', true));
                    })
                    ->orWhereHas('auxProducts', fn ($query) => $query->where('is_enabled', true));
            })
            ->orderBy('order')
            ->get([
                'id', 'name', 'parent_id', '_lft', '_rgt',
                'order', 'order_home', 'is_home', 'descriptive', 'image'
            ])
            ->toTree();

        return $this->sendResponse($sections);
    }
}
