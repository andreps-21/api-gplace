<?php

namespace App\Http\Controllers\API\Admin;

use App\Enums\TypeImage;
use App\Http\Controllers\API\BaseController;
use App\Models\InterfacePosition;
use App\Models\SizeImage;
use Illuminate\Http\Request;

class SizeImageAdminController extends BaseController
{
    public function index(Request $request)
    {
        $query = SizeImage::query()
            ->with('interfacePositions')
            ->orderBy('name')
            ->when($request->filled('is_enabled'), fn ($q) => $q->where('is_enabled', filter_var($request->is_enabled, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type));

        return $this->sendResponse(
            $request->boolean('all') ? $query->get() : $query->paginate((int) $request->get('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $interfacePositions = $data['interface_positions'] ?? [];
        unset($data['interface_positions']);

        $item = SizeImage::create($data);
        $item->interfacePositions()->sync($interfacePositions);

        return $this->sendResponse($item->fresh('interfacePositions'), 'Registro criado com sucesso.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(SizeImage::with('interfacePositions')->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $item = SizeImage::findOrFail($id);
        $data = $request->validate($this->rules());
        $interfacePositions = $data['interface_positions'] ?? [];
        unset($data['interface_positions']);

        $item->fill($data)->save();
        $item->interfacePositions()->sync($interfacePositions);

        return $this->sendResponse($item->fresh('interfacePositions'), 'Registro atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $item = SizeImage::findOrFail($id);
        $item->interfacePositions()->detach();
        $item->delete();

        return $this->sendResponse([], 'Registro deletado com sucesso.');
    }

    public function options()
    {
        return $this->sendResponse([
            'types' => TypeImage::types(),
            'interface_positions' => InterfacePosition::query()
                ->where('is_enabled', true)
                ->orderBy('id_position')
                ->get(['id', 'id_position', 'position_name']),
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'size_width' => ['required', 'integer', 'min:1', 'max:99999'],
            'size_height' => ['required', 'integer', 'min:1', 'max:99999'],
            'is_enabled' => ['required', 'boolean'],
            'type' => ['required', 'integer'],
            'code' => ['nullable', 'string', 'max:191'],
            'interface_positions' => ['nullable', 'array'],
            'interface_positions.*' => ['integer', 'exists:interface_positions,id'],
        ];
    }
}
