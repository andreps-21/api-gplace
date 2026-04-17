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

        /*
         * Secções: modelo com NestedSet (`_lft` / `_rgt`). Evitar `orderBy('order')` — a coluna `order`
         * pode não existir em bases antigas; a ordem hierárquica correcta é por `_lft`.
         * Incluir `_lft`/`_rgt` no select para o NodeTrait não falhar com colunas em falta.
         */
        $sections = Section::query()
            ->where('store_id', $storeId)
            ->where('is_enabled', true)
            ->defaultOrder()
            ->get(['id', 'name', 'parent_id', '_lft', '_rgt'])
            ->map(static fn (Section $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'parent_id' => $s->parent_id,
            ])
            ->values()
            ->all();

        return $this->sendResponse([
            'sections' => $sections,
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
