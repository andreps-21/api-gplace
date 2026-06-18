<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class PermissionListController extends BaseController
{
    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function index(Request $request)
    {
        if (! $request->user()?->can('permissions_view') && ! $request->user()?->can('roles_view')) {
            abort(403);
        }

        $perPage = min(500, max(5, (int) $request->query('per_page', 15)));

        $paginator = Permission::query()
            ->orderBy('description')
            ->paginate($perPage);

        return $this->sendResponse($paginator);
    }

    public function __invoke(Request $request)
    {
        return $this->index($request);
    }

    public function store(Request $request)
    {
        if (! $request->user()?->can('permissions_create')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $permission = Permission::query()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'guard_name' => $request->input('guard_name', 'web'),
        ]);
        $this->forgetPermissionCache();

        return $this->sendResponse($permission, 'Permissão criada.', 201);
    }

    public function show(Request $request, int $id)
    {
        if (! $request->user()?->can('permissions_view') && ! $request->user()?->can('roles_view')) {
            abort(403);
        }

        return $this->sendResponse(Permission::query()->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        if (! $request->user()?->can('permissions_edit')) {
            abort(403);
        }

        $permission = Permission::query()->findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($permission->id));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $permission->fill([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'guard_name' => $request->input('guard_name', $permission->guard_name),
        ])->save();
        $this->forgetPermissionCache();

        return $this->sendResponse($permission->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        if (! $request->user()?->can('permissions_delete')) {
            abort(403);
        }

        $permission = Permission::query()->findOrFail($id);
        try {
            $permission->delete();
            $this->forgetPermissionCache();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $primaryKey = null): array
    {
        return [
            'name' => ['required', 'max:50', Rule::unique('permissions', 'name')->ignore($primaryKey)],
            'description' => ['required', 'max:80'],
            'guard_name' => ['nullable', 'max:50'],
        ];
    }
}
