<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminActionPermission
{
    private const RESOURCE_PERMISSIONS = [
        'banners' => 'banners',
        'brands' => 'brands',
        'business-units' => 'businessunits',
        'cities' => 'cities',
        'coupons' => 'coupons',
        'customers' => 'customers',
        'erp' => 'erp',
        'freights' => 'freights',
        'grids' => 'grid',
        'home-blocks' => 'sections',
        'interface-positions' => 'interface-positions',
        'leads' => 'leads',
        'measurement-units' => 'measurement-units',
        'parameters' => 'parameters',
        'payment-methods-admin' => 'payment-methods',
        'products' => 'products',
        'quick-sale' => 'orders',
        'salesmen' => 'salesman',
        'sections' => 'sections',
        'size-images' => 'size-image',
        'social-medias' => 'social-medias',
        'states' => 'states',
        'stock-lots' => 'products',
        'store-catalogs' => 'catalogs',
        'store-faqs' => 'faq',
        'store-roles' => 'roles',
        'store-settings' => 'settings',
        'store-users' => 'users',
        'stores' => 'stores',
        'tenants' => 'tenants',
        'tokens' => 'tokens',
        'warehouses' => 'products',
    ];

    public function handle(Request $request, Closure $next)
    {
        $permission = $this->permissionFor($request);

        if ($permission && ! $request->user()?->can($permission)) {
            abort(403);
        }

        return $next($request);
    }

    private function permissionFor(Request $request): ?string
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
            return null;
        }

        $segments = $request->segments();
        $adminIndex = array_search('admin', $segments, true);
        if ($adminIndex === false) {
            return null;
        }

        $resource = $segments[$adminIndex + 1] ?? null;
        if (! $resource || ! isset(self::RESOURCE_PERMISSIONS[$resource])) {
            return null;
        }

        $base = self::RESOURCE_PERMISSIONS[$resource];

        if ($resource === 'store-settings') {
            return 'settings_edit';
        }

        if ($resource === 'products' && ($segments[$adminIndex + 3] ?? null) === 'images') {
            return 'products_edit';
        }

        if ($resource === 'store-roles' && ($segments[$adminIndex + 3] ?? null) === 'permissions') {
            return 'roles_edit';
        }

        return match (true) {
            $request->isMethod('POST') => "{$base}_create",
            $request->isMethod('PUT'), $request->isMethod('PATCH') => "{$base}_edit",
            $request->isMethod('DELETE') => "{$base}_delete",
            default => null,
        };
    }
}
