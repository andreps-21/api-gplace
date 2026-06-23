<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\ProductFormTemplate;
use App\Models\StoreProductFieldSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreProductParameterController extends BaseController
{
    public const FIELD_TYPES = [
        'fixed',
        'text',
        'number',
        'money',
        'date',
        'boolean',
        'select',
        'multiselect',
        'color',
        'url',
    ];

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);

        return $this->sendResponse([
            'templates' => ProductFormTemplate::query()
                ->where('is_enabled', true)
                ->with('fields')
                ->orderBy('name')
                ->get(),
            'current_template_id' => $this->currentTemplateId($storeId),
            'fields' => $this->effectiveFields($storeId),
            'field_types' => self::FIELD_TYPES,
        ]);
    }

    public function update(Request $request)
    {
        $storeId = $this->storeId($request);

        $validator = Validator::make($request->all(), [
            'template_id' => ['nullable', 'integer', 'exists:product_form_templates,id'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field_key' => ['required', 'string', 'max:80'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', Rule::in(self::FIELD_TYPES)],
            'fields.*.is_fixed' => ['required', 'boolean'],
            'fields.*.is_visible' => ['required', 'boolean'],
            'fields.*.is_required' => ['required', 'boolean'],
            'fields.*.show_on_ecommerce' => ['required', 'boolean'],
            'fields.*.show_as_filter' => ['required', 'boolean'],
            'fields.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'fields.*.options' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();
        $templateId = $validated['template_id'] ?? null;

        DB::transaction(function () use ($storeId, $validated, $templateId) {
            StoreProductFieldSetting::query()->where('store_id', $storeId)->delete();

            foreach ($validated['fields'] as $field) {
                StoreProductFieldSetting::query()->create([
                    'store_id' => $storeId,
                    'product_form_template_id' => $templateId,
                    'field_key' => $this->normalizeFieldKey($field['field_key']),
                    'label' => $field['label'],
                    'type' => $field['is_fixed'] ? 'fixed' : $field['type'],
                    'is_fixed' => (bool) $field['is_fixed'],
                    'is_visible' => (bool) $field['is_visible'],
                    'is_required' => (bool) $field['is_required'],
                    'show_on_ecommerce' => (bool) $field['show_on_ecommerce'],
                    'show_as_filter' => (bool) $field['show_as_filter'],
                    'options' => $field['options'] ?? null,
                    'sort_order' => (int) $field['sort_order'],
                ]);
            }
        });

        return $this->sendResponse([
            'current_template_id' => $this->currentTemplateId($storeId),
            'fields' => $this->effectiveFields($storeId),
        ], 'Parâmetros de produto atualizados.');
    }

    public static function effectiveFieldsForStore(int $storeId)
    {
        $fields = StoreProductFieldSetting::query()
            ->where('store_id', $storeId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($fields->isNotEmpty()) {
            return $fields;
        }

        $template = ProductFormTemplate::query()
            ->where('code', 'generic')
            ->with('fields')
            ->first();

        return $template?->fields ?? collect();
    }

    private function effectiveFields(int $storeId)
    {
        return self::effectiveFieldsForStore($storeId)->values();
    }

    private function currentTemplateId(int $storeId): ?int
    {
        $templateId = StoreProductFieldSetting::query()
            ->where('store_id', $storeId)
            ->whereNotNull('product_form_template_id')
            ->value('product_form_template_id');

        if ($templateId) {
            return (int) $templateId;
        }

        return ProductFormTemplate::query()->where('code', 'generic')->value('id');
    }

    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    private function normalizeFieldKey(string $fieldKey): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', trim($fieldKey)));
    }
}
