<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Family;
use App\Models\MeasurementUnit;
use App\Models\Presentation;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Dados auxiliares para formulários de produto (UM, família, apresentação).
 */
class ProductFormMetaController extends BaseController
{
    public function __invoke(Request $request)
    {
        $storeId = (int) $request->attributes->get('store')['id'];

        return $this->sendResponse([
            'sections' => Section::query()
                ->where('store_id', $storeId)
                ->where('is_enabled', true)
                ->orderBy('order')
                ->get(['id', 'name', 'parent_id']),
            'measurement_units' => MeasurementUnit::query()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name', 'initials']),
            'families' => Family::query()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'presentations' => Presentation::query()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
