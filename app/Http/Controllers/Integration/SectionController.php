<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends BaseController
{
    public function index(Request $request)
    {
        $store = $request->get('store')['id'];

        $sections = Section::query()
            ->orderBy('order')
            ->where('store_id', $store)
            ->get()
            ->toTree();

        return $this->sendResponse($sections);
    }

    public function store(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $inputs = $request->all();
        $inputs['use'] = 1;
        $inputs['store_id'] = $request->get('store')['id'];
        Section::create($inputs);

        return $this->sendResponse([], "Registro criado com sucesso.");
    }

    public function show(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $section = Section::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();

        return $this->sendResponse($section);
    }

    public function update(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $section = Section::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();


        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $id)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $inputs = $request->all();
        $section->fill($inputs)->save();

        return $this->sendResponse([], "Registro atualizado com sucesso.");
    }

    public function destroy(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $section = Section::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();

        try {
            $section->delete();
            return $this->sendResponse([], "Registro deletado com sucesso.");
        } catch (\Exception $e) {
            return $this->sendError("Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.", [], 403);
        }
    }




    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'min:3', 'max:35', Rule::unique('sections')->ignore($primaryKey)],
            'descriptive' => ['nullable', 'max:120'],
            'type' => ['required', 'in:A,S'],
            'is_enabled' => ['required'],
            'order' => ['required', 'integer'],
            'is_home' => ['required', 'boolean'],
            'parent_id' => [Rule::requiredIf($request->type == 'A')],
            'order_home' => [Rule::requiredIf(boolval($request->is_home)),  'nullable', 'integer', 'min:0']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
