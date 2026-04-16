<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeasurementUnitController extends BaseController
{
    public function index()
    {
        $measurements = MeasurementUnit::query()
            ->orderBy('initials')
            ->paginate(25);

        return $this->sendResponse($measurements);
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
        MeasurementUnit::create($inputs);

        return $this->sendResponse([], "Registro criado com sucesso.");
    }

    public function show($id)
    {
        $measurement = MeasurementUnit::findOrFail($id);

        return $this->sendResponse($measurement);
    }

    public function update(Request $request, $id)
    {
        $measurement = MeasurementUnit::query()
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
        $measurement->fill($inputs)->save();

        return $this->sendResponse([], "Registro atualizado com sucesso.");
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('store')['tenant_id'];

        $measurement = MeasurementUnit::query()
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->where('id', $id)
            ->firstOrFail();

        try {
            $measurement->delete();
            return $this->sendResponse([], "Registro deletado com sucesso.");
        } catch (\Exception $e) {
            return $this->sendError("Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.", [], 403);
        }
    }


    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'initials' => ['required', 'max:4', Rule::unique('measurement_units')->ignore($primaryKey)],
            'name' => ['required', 'max:20'],
            'is_enabled' => ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
