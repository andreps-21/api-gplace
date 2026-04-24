<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
| Rotas /api/v1/* com nomes prefixados (api.v1.*) para não colidir com route names do Blade
| (ex.: cities.index, states.index, products.index) ao correr `php artisan route:cache`.
| A rota PagSeguro mantém o nome global `pagseguro.notification` (config dos pacotes).
*/
Route::prefix('v1')->group(function () {
    Route::post('/pagseguro/notification', [
        'uses' => '\laravel\pagseguro\Platform\Laravel5\NotificationController@notification',
    ])->name('pagseguro.notification');

    Route::name('api.v1.')->group(function () {
        /*
        | Login e recuperação de senha sem header «app» (painel / Blade).
        */
        Route::prefix('auth')->group(function () {
            Route::post('login', [App\Http\Controllers\API\SessionController::class, 'store']);
            Route::post('password/email', App\Http\Controllers\API\ForgotPasswordController::class);
            Route::post('password/code/check', App\Http\Controllers\API\CodeCheckController::class);
            Route::post('password/reset', App\Http\Controllers\API\ResetPasswordController::class);
        });

        /*
        | Área autenticada do painel: Bearer Sanctum + loja inferida do utilizador
        | (header «app» opcional para escolher loja quando o user tem várias).
        */
        Route::middleware(['auth:sanctum', 'user_store'])->group(function () {
            Route::prefix('auth')->group(function () {
                Route::delete('logout', [App\Http\Controllers\API\SessionController::class, 'destroy']);
                Route::get('profile', [App\Http\Controllers\API\ProfileController::class, 'show']);
                Route::put('profile', [App\Http\Controllers\API\ProfileController::class, 'update']);
                Route::post('change-password', App\Http\Controllers\API\ChangePasswordController::class);
            });

            Route::get('dashboard/stats', [App\Http\Controllers\API\DashboardController::class, 'stats']);
            Route::get('dashboard/sales-summary', [App\Http\Controllers\API\DashboardController::class, 'salesSummary']);
            Route::get('dashboard/recent-sales', [App\Http\Controllers\API\DashboardController::class, 'recentSales']);
            Route::get('dashboard/orders-yearly', [App\Http\Controllers\API\DashboardController::class, 'ordersYearly']);
            Route::get('dashboard/orders-daily', [App\Http\Controllers\API\DashboardController::class, 'ordersDaily']);
            Route::get('dashboard/faturamento', [App\Http\Controllers\API\DashboardController::class, 'faturamento']);
            Route::get('dashboard/top-products', [App\Http\Controllers\API\DashboardController::class, 'topProducts']);
            Route::get('sales', App\Http\Controllers\API\SalesListController::class);
            Route::get('establishments/stats', App\Http\Controllers\API\EstablishmentStatsController::class);
            Route::get('establishments', App\Http\Controllers\API\EstablishmentListController::class);

            /** Cabeçalho do Next — inbox ainda pode ser expandido (evita 404 em chamadas paralelas). */
            Route::get('notifications/inbox', fn () => response()->json([
                'message' => '',
                'data' => ['items' => [], 'unread_count' => 0],
            ]));
            Route::post('notifications/dismiss', fn () => response()->json(['message' => '', 'data' => null]));
            Route::post('notifications/dismiss-all', fn () => response()->json(['message' => '', 'data' => null]));

            Route::prefix('admin')->group(function () {
                Route::get('store-settings', [App\Http\Controllers\API\Admin\StoreSettingController::class, 'show']);
                Route::put('store-settings', [App\Http\Controllers\API\Admin\StoreSettingController::class, 'update']);

                Route::get('parameters', [App\Http\Controllers\API\Admin\ParameterController::class, 'index']);
                Route::post('parameters', [App\Http\Controllers\API\Admin\ParameterController::class, 'store']);
                Route::get('parameters/{id}', [App\Http\Controllers\API\Admin\ParameterController::class, 'show']);
                Route::put('parameters/{id}', [App\Http\Controllers\API\Admin\ParameterController::class, 'update']);
                Route::delete('parameters/{id}', [App\Http\Controllers\API\Admin\ParameterController::class, 'destroy']);

                Route::get('store-users', App\Http\Controllers\API\Admin\StoreUserListController::class);
                Route::post('store-users/attach', [App\Http\Controllers\API\Admin\StoreUserPivotController::class, 'attach']);
                Route::delete('store-users/detach/{userId}', [App\Http\Controllers\API\Admin\StoreUserPivotController::class, 'detach']);

                Route::get('store-roles', App\Http\Controllers\API\Admin\StoreRoleListController::class);
                Route::get('store-roles/{id}', [App\Http\Controllers\API\Admin\StoreRoleController::class, 'show']);
                Route::put('store-roles/{id}/permissions', [App\Http\Controllers\API\Admin\StoreRoleController::class, 'syncPermissions']);
                Route::get('permissions', App\Http\Controllers\API\Admin\PermissionListController::class);
                Route::get('product-form-meta', App\Http\Controllers\API\Admin\ProductFormMetaController::class);
                /** Mesmo que `GET /payment-methods` do catálogo (`app`), mas com loja do `user_store` — evita 403 no painel sem `NEXT_PUBLIC_APP_TOKEN`. */
                Route::get('payment-methods', [App\Http\Controllers\API\PaymentMethodController::class, 'index']);

                Route::get('store-faqs', [App\Http\Controllers\API\Admin\StoreFaqController::class, 'index']);
                Route::post('store-faqs', [App\Http\Controllers\API\Admin\StoreFaqController::class, 'store']);
                Route::get('store-faqs/{id}', [App\Http\Controllers\API\Admin\StoreFaqController::class, 'show']);
                Route::put('store-faqs/{id}', [App\Http\Controllers\API\Admin\StoreFaqController::class, 'update']);
                Route::delete('store-faqs/{id}', [App\Http\Controllers\API\Admin\StoreFaqController::class, 'destroy']);

                Route::get('store-catalogs', [App\Http\Controllers\API\Admin\StoreCatalogController::class, 'index']);
                Route::post('store-catalogs', [App\Http\Controllers\API\Admin\StoreCatalogController::class, 'store']);
                Route::get('store-catalogs/{id}', [App\Http\Controllers\API\Admin\StoreCatalogController::class, 'show']);
                Route::put('store-catalogs/{id}', [App\Http\Controllers\API\Admin\StoreCatalogController::class, 'update']);
                Route::delete('store-catalogs/{id}', [App\Http\Controllers\API\Admin\StoreCatalogController::class, 'destroy']);

                Route::get('tokens', [App\Http\Controllers\API\Admin\TokenAdminController::class, 'index']);
                Route::post('tokens', [App\Http\Controllers\API\Admin\TokenAdminController::class, 'store']);
                Route::get('tokens/{id}', [App\Http\Controllers\API\Admin\TokenAdminController::class, 'show']);
                Route::put('tokens/{id}', [App\Http\Controllers\API\Admin\TokenAdminController::class, 'update']);
                Route::delete('tokens/{id}', [App\Http\Controllers\API\Admin\TokenAdminController::class, 'destroy']);

                Route::get('tenants', [App\Http\Controllers\API\Admin\TenantAdminController::class, 'index']);
                Route::post('tenants', [App\Http\Controllers\API\Admin\TenantAdminController::class, 'store']);
                Route::get('tenants/{id}', [App\Http\Controllers\API\Admin\TenantAdminController::class, 'show']);
                Route::put('tenants/{id}', [App\Http\Controllers\API\Admin\TenantAdminController::class, 'update']);
                Route::delete('tenants/{id}', [App\Http\Controllers\API\Admin\TenantAdminController::class, 'destroy']);

                Route::get('customers', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'index']);
                Route::post('customers/quick', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'storeQuick']);
                Route::post('customers', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'store']);
                Route::get('customers/{id}', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'show']);
                Route::put('customers/{id}', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'update']);
                Route::delete('customers/{id}', [App\Http\Controllers\API\Admin\CustomerAdminController::class, 'destroy']);

                Route::get('leads', [App\Http\Controllers\API\Admin\LeadAdminController::class, 'index']);
                Route::post('leads', [App\Http\Controllers\API\Admin\LeadAdminController::class, 'store']);
                Route::get('leads/{id}', [App\Http\Controllers\API\Admin\LeadAdminController::class, 'show']);
                Route::put('leads/{id}', [App\Http\Controllers\API\Admin\LeadAdminController::class, 'update']);
                Route::delete('leads/{id}', [App\Http\Controllers\API\Admin\LeadAdminController::class, 'destroy']);

                Route::get('stores', [App\Http\Controllers\API\Admin\StoreAdminController::class, 'index']);
                Route::post('stores', [App\Http\Controllers\API\Admin\StoreAdminController::class, 'store']);
                Route::get('stores/{id}', [App\Http\Controllers\API\Admin\StoreAdminController::class, 'show']);
                Route::put('stores/{id}', [App\Http\Controllers\API\Admin\StoreAdminController::class, 'update']);
                Route::delete('stores/{id}', [App\Http\Controllers\API\Admin\StoreAdminController::class, 'destroy']);

                Route::get('salesmen', [App\Http\Controllers\API\Admin\SalesmanAdminController::class, 'index']);
                Route::post('salesmen', [App\Http\Controllers\API\Admin\SalesmanAdminController::class, 'store']);
                Route::get('salesmen/{id}', [App\Http\Controllers\API\Admin\SalesmanAdminController::class, 'show']);
                Route::put('salesmen/{id}', [App\Http\Controllers\API\Admin\SalesmanAdminController::class, 'update']);
                Route::delete('salesmen/{id}', [App\Http\Controllers\API\Admin\SalesmanAdminController::class, 'destroy']);

                Route::get('products/resolve', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'resolve']);
                Route::get('products/fiscal-suggest', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'fiscalSuggest']);
                Route::get('products/metrics', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'metrics']);
                Route::get('products', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'index']);
                Route::post('products', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'store']);
                Route::get('products/{id}', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'show']);
                Route::put('products/{id}', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'update']);
                /** Upload/replace imagens do produto (multipart: images[]). */
                Route::post('products/{id}/images', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'uploadImages']);
                Route::delete('products/{id}', [App\Http\Controllers\API\Admin\ProductAdminController::class, 'destroy']);

                Route::get('warehouses', [App\Http\Controllers\API\Admin\WarehouseAdminController::class, 'index']);
                Route::post('warehouses', [App\Http\Controllers\API\Admin\WarehouseAdminController::class, 'store']);
                Route::get('stock-movements', [App\Http\Controllers\API\Admin\StockMovementAdminController::class, 'index']);
                Route::get('stock-lots', [App\Http\Controllers\API\Admin\StockLotAdminController::class, 'index']);
                Route::post('stock-lots', [App\Http\Controllers\API\Admin\StockLotAdminController::class, 'store']);

                Route::get('quick-sale/next-code', [App\Http\Controllers\API\Admin\QuickSaleController::class, 'nextCode']);
                Route::post('quick-sale', [App\Http\Controllers\API\Admin\QuickSaleController::class, 'store']);

                Route::get('orders', [App\Http\Controllers\API\Admin\OrderAdminController::class, 'index']);
                Route::get('orders/{id}', [App\Http\Controllers\API\Admin\OrderAdminController::class, 'show']);

                Route::apiResource('sections', App\Http\Controllers\Integration\SectionController::class);
                Route::apiResource('brands', App\Http\Controllers\Integration\BrandController::class);
                Route::apiResource('measurement-units', App\Http\Controllers\Integration\MeasurementUnitController::class);
            });
        });

        /*
        | Ecommerce / catálogo público: identifica a loja pelo header «app».
        | Pedidos e moradas do cliente continuam com app + Sanctum.
        */
        Route::middleware(['app'])->group(function () {
            Route::prefix('auth')->group(function () {
                Route::post('users', [App\Http\Controllers\API\UserController::class, 'store']);
                Route::post('user-lead', [App\Http\Controllers\API\UserLeadController::class, 'store']);
            });

            Route::apiResource('products', App\Http\Controllers\API\ProductController::class)->only('index', 'show');
            Route::apiResource('faqs', App\Http\Controllers\API\FaqController::class)->only('index', 'show');
            Route::apiResource('catalogs', App\Http\Controllers\API\CatalogController::class)->only('index', 'show');
            Route::apiResource('leads', App\Http\Controllers\API\LeadController::class)->only(['store']);
            Route::get('brands', [App\Http\Controllers\API\BrandController::class, 'index']);
            Route::get('sections', [App\Http\Controllers\API\SectionController::class, 'index']);
            Route::get('sections-home', [App\Http\Controllers\API\SectionHomeController::class, 'index']);
            Route::post('calc-freight', App\Http\Controllers\API\CalcFreightController::class);
            Route::get('banners', [App\Http\Controllers\API\BannerController::class, 'index']);
            Route::post('pagseguro-installments', App\Http\Controllers\API\GetInstallmentsController::class);
            Route::get('parameters', [App\Http\Controllers\API\ParameterController::class, 'index']);
            Route::post('contact', App\Http\Controllers\API\ContactController::class);
            Route::get('payment-methods', [App\Http\Controllers\API\PaymentMethodController::class, 'index']);
            Route::get('settings', [App\Http\Controllers\API\SettingsController::class, 'index']);
            Route::get('public-key', App\Http\Controllers\API\PublicKeyController::class);
            Route::get('pagseguro-session', App\Http\Controllers\API\PagseguroSessionController::class);
            Route::post('pagseguro-installments', App\Http\Controllers\API\PagseguroInstallmentController::class);
            Route::get('home', App\Http\Controllers\API\HomeController::class);

            Route::get('coupons', [App\Http\Controllers\API\CouponController::class, 'index']);
            Route::post('validate-coupon', App\Http\Controllers\API\ValidateCouponController::class);
            Route::get('salesman', [App\Http\Controllers\API\SalesmanController::class, 'index']);

            Route::middleware('auth:sanctum')->group(function () {
                Route::apiResource('orders', App\Http\Controllers\API\OrderController::class)->only(['index', 'show', 'store']);
                Route::apiResource('addresses', App\Http\Controllers\API\AddressController::class);
            });
        });

    Route::get('get-person-by-nif', App\Http\Controllers\API\GetPersonByNifController::class);
    Route::post('get-user-by-nif', App\Http\Controllers\API\GetUserByNifController::class);

    Route::apiResource('states', App\Http\Controllers\API\StateController::class)->only(['index', 'show']);
    Route::apiResource('cities', App\Http\Controllers\API\CityController::class)
        ->only(['index', 'show']);

    Route::post('/variation', [App\Http\Controllers\API\ProductController::class, 'getGrid']);
    Route::delete('/variation/{id}/delete', [App\Http\Controllers\API\VariationController::class, 'destroy']);

    Route::get('inactivate-coupon/{id}', [App\Http\Controllers\API\CouponController::class, 'inactivate']);

    Route::prefix('public')->group(function () {
        Route::post('change-status-orders', App\Http\Controllers\API\ChangeOrderStatusController::class);
    });

    Route::middleware(['auth.integration'])
        ->prefix('integration')
        ->name('integration.')
        ->group(function () {
            Route::apiResource('brands', App\Http\Controllers\Integration\BrandController::class);
            Route::apiResource('products', App\Http\Controllers\Integration\ProductController::class);
            Route::apiResource('sections', App\Http\Controllers\Integration\SectionController::class);
            Route::apiResource('measurement-unit', App\Http\Controllers\Integration\MeasurementUnitController::class);
            Route::apiResource('orders', App\Http\Controllers\Integration\OrderController::class)->only(['index', 'show', 'update']);
        });

    });
});
