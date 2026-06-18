<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\InterfacePosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterfacePositionAdminController extends BaseController
{
    public function index(Request $request)
    {
        $data = InterfacePosition::query()
            ->orderBy('id_position')
            ->when($request->filled('is_enabled'), fn ($q) => $q->where('is_enabled', filter_var($request->is_enabled, FILTER_VALIDATE_BOOLEAN)))
            ->get();

        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $item = InterfacePosition::create($data);

        return $this->sendResponse($item, 'Registro criado com sucesso.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(InterfacePosition::findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $item = InterfacePosition::findOrFail($id);
        $item->fill($request->validate($this->rules($id)))->save();

        return $this->sendResponse($item->fresh(), 'Registro atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        InterfacePosition::findOrFail($id)->delete();

        return $this->sendResponse([], 'Registro deletado com sucesso.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'id_position' => ['required', 'string', 'max:10', Rule::unique('interface_positions', 'id_position')->ignore($id)],
            'position_name' => ['required', 'string', 'max:60'],
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}
