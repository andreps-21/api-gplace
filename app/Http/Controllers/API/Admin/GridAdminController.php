<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Grid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GridAdminController extends BaseController
{
    public function index(Request $request)
    {
        $query = Grid::query()
            ->with('variation')
            ->orderBy('grid');

        return $this->sendResponse(
            $request->boolean('all') ? $query->get() : $query->paginate((int) $request->get('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $variations = $data['variations'];
        unset($data['variations']);

        $grid = DB::transaction(function () use ($data, $variations) {
            $grid = Grid::create($data);
            $grid->variation()->createMany($variations);
            return $grid;
        });

        return $this->sendResponse($grid->fresh('variation'), 'Registro criado com sucesso.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(Grid::with('variation')->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $grid = Grid::findOrFail($id);
        $data = $request->validate($this->rules());
        $variations = $data['variations'];
        unset($data['variations']);

        DB::transaction(function () use ($grid, $data, $variations) {
            $grid->fill($data)->save();
            $keepIds = [];

            foreach ($variations as $variation) {
                $id = $variation['id'] ?? null;
                unset($variation['id']);
                $child = $grid->variation()->updateOrCreate(['id' => $id], $variation);
                $keepIds[] = $child->id;
            }

            $grid->variation()->whereNotIn('id', $keepIds)->delete();
        });

        return $this->sendResponse($grid->fresh('variation'), 'Registro atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        Grid::with('variation')->findOrFail($id)->delete();

        return $this->sendResponse([], 'Registro deletado com sucesso.');
    }

    private function rules(): array
    {
        return [
            'grid' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:40'],
            'is_enabled' => ['required', 'boolean'],
            'type' => ['required', 'string', 'max:20'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.id' => ['nullable', 'integer', 'exists:variations,id'],
            'variations.*.abbreviation' => ['required', 'string', 'max:20'],
            'variations.*.variation' => ['required', 'string', 'max:100'],
            'variations.*.is_enabled' => ['required', 'boolean'],
            'variations.*.representation' => ['required', 'string', 'max:191'],
        ];
    }
}
