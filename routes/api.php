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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::middleware(['app'])->group(function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('login', [App\Http\Controllers\API\SessionController::class, 'store']);
            Route::post('users', [App\Http\Controllers\API\UserController::class, 'store']);
            Route::post('user-lead', [App\Http\Controllers\API\UserLeadController::class, 'store']);


            Route::post('password/email',  App\Http\Controllers\API\ForgotPasswordController::class);
            Route::post('password/code/check', App\Http\Controllers\API\CodeCheckController::class);
            Route::post('password/reset', App\Http\Controllers\API\ResetPasswordController::class);


            Route::middleware('auth:api')->group(function () {
                Route::delete('logout', [App\Http\Controllers\API\SessionController::class, 'destroy']);
                Route::get('profile', [App\Http\Controllers\API\ProfileController::class, 'show']);
                Route::put('profile', [App\Http\Controllers\API\ProfileController::class, 'update']);
                Route::post('change-password', App\Http\Controllers\API\ChangePasswordController::class);
            });
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

        Route::middleware('auth:api')->group(function () {
            Route::apiResource('orders', App\Http\Controllers\API\OrderController::class)->only(['index', 'show', 'store']);
            Route::apiResource('addresses', App\Http\Controllers\API\AddressController::class);
        });
    });

    Route::post('/pagseguro/notification', [
        'uses' => '\laravel\pagseguro\Platform\Laravel5\NotificationController@notification',
        'as' => 'pagseguro.notification',
    ]);

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

    Route::post('pagseguro/notification',  App\Http\Controllers\API\NotificationPagseguroController::class);
});
