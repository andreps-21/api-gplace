<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $paginator = Permission::query()
            ->orderBy('description')
            ->paginate($perPage);

        return $this->sendResponse($paginator);
    }
}
