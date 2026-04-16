<?php

use App\Actions\IntegrationAction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::get('melhor-envio', App\Http\Controllers\API\MelhorEnvioCallbackController::class)->name('auth.melhor-envio');

Route::post('password/reset-code', [
    'as' => 'password.update-code',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@reset'
]);

Route::post('password/check-code', [
    'as' => 'password.check-code',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@CodeCheck'
]);

Route::get('password/reset-code', [
    'as' => 'password.reset-code',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm'
]);

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('cities', [App\Http\Controllers\Admin\CityController::class, 'index'])->name('cities.index');
    Route::get('states', [App\Http\Controllers\Admin\StateController::class, 'index'])->name('states.index');
    Route::get('profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::get('change-password', [App\Http\Controllers\Admin\PasswordController::class, 'edit'])->name('change-password.edit');
    Route::put('change-password', [App\Http\Controllers\Admin\PasswordController::class, 'update'])->name('change-password.update');
    Route::get('customers/{id}/addresses', [App\Http\Controllers\Admin\CustomerController::class, 'addresses'])->name('customer.address');
    Route::get('change-first-password', [App\Http\Controllers\Admin\ChangeFirtsPasswordController::class, 'edit'])->name('auth.change-first-password.edit');
    Route::put('change-first-password', [App\Http\Controllers\Admin\ChangeFirtsPasswordController::class, 'update'])->name('auth.change-first-password.update');
    Route::get('change-store/{id}', App\Http\Controllers\Admin\ChangeStoreSessionController::class)->name('change.store');


    Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class);
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('parameters', App\Http\Controllers\Admin\ParameterController::class);


    Route::resource('brands', App\Http\Controllers\Admin\BrandController::class);
    Route::resource('grid', App\Http\Controllers\Admin\GridController::class);
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->except(['edit', 'update']);
    Route::get('orders/print/{id}', App\Http\Controllers\Admin\OrderPrintController::class)->name('orders.print');
    Route::resource('salesman', App\Http\Controllers\Admin\SalesmanController::class);
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('measurement-units', App\Http\Controllers\Admin\MeasurementUnitController::class);
    Route::resource('families', App\Http\Controllers\Admin\FamilyController::class);
    Route::resource('presentations', App\Http\Controllers\Admin\PresentationController::class);
    Route::resource('payment-methods', App\Http\Controllers\Admin\PaymentMethodController::class);
    Route::resource('social-medias', App\Http\Controllers\Admin\SocialMediaController::class);
    Route::resource('erp', App\Http\Controllers\Admin\ErpController::class);
    Route::resource('product-reviews', App\Http\Controllers\Admin\ProductReviewController::class);
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::resource('professions', App\Http\Controllers\Admin\ProfessionController::class);
    Route::resource('services-area', App\Http\Controllers\Admin\ServiceAreaController::class);
    Route::resource('partners', App\Http\Controllers\Admin\PartnerController::class);
    Route::resource('providers', App\Http\Controllers\Admin\ProviderController::class);
    Route::resource('sections', App\Http\Controllers\Admin\SectionController::class);
    Route::resource('faq', App\Http\Controllers\Admin\FaqController::class);
    Route::resource('catalogs', App\Http\Controllers\Admin\CatalogController::class);
    Route::resource('leads', App\Http\Controllers\Admin\LeadController::class);
    Route::resource('product-providers', App\Http\Controllers\Admin\ProductProviderController::class);
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class);
    Route::resource('business-units', App\Http\Controllers\Admin\BusinessUnitController::class);
    Route::resource('freights', App\Http\Controllers\Admin\FreightController::class);
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    Route::resource('tokens', App\Http\Controllers\Admin\TokenController::class);
    Route::resource('size-image', App\Http\Controllers\Admin\SizeImageController::class);
    Route::resource('interface-positions', App\Http\Controllers\Admin\InterfacePositionController::class);

    Route::get('/exports/customers', [App\Http\Controllers\Admin\ExportCustomerController::class, 'index'])->name('customer.export');
    Route::get('/exports/leads', [App\Http\Controllers\Admin\ExportLeadsController::class, 'index'])->name('leads.export');

    Route::get('products-report', App\Http\Controllers\Admin\ProductsReportController::class)->name('products.report');
    Route::get('orders-report', App\Http\Controllers\Admin\OrdersReportController::class)->name('orders.report');

    Route::resource('tenants', App\Http\Controllers\Admin\TenantsController::class);
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    Route::resource('stores', App\Http\Controllers\Admin\StoreController::class);
});


Route::get('credit-card', function () {
    return view('credit_card');
});
