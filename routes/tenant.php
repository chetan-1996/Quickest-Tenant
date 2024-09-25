<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
//use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\App\Auth\LoginController;
use App\Http\Controllers\App\Auth\RegisterController;
use App\Http\Controllers\App\Auth\ForgotPasswordController;
use App\Http\Controllers\App\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Route::middleware([
//     'web',
//     InitializeTenancyByDomain::class,
//     PreventAccessFromCentralDomains::class,
//     //InitializeTenancyByPath::class,
// ])->group([
//     'prefix' => '/{tenant}', 'middleware' => [InitializeTenancyByPath::class],
// ], function () {
//     Route::get('/', function () {
//         return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
//     });

Route::group([
    'prefix' => '/{tenant}',
    //'name' => 'tenant',
    'middleware' => [
        'web', 
        InitializeTenancyByPath::class,
        //InitializeTenancyByDomain::class,
        //PreventAccessFromCentralDomains::class,
        //InitializeTenancyByRequestData::$header = null,
        //InitializeTenancyByRequestData::$queryParameter = null,
    ],
], function () {
    //dd(\App\Models\User::all());
    // Route::get('/', function () {
    //     return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    // });
    
    Route::controller(\App\Http\Controllers\App\AuthOtpController::class)->group(function () {//dd('{tenant}.');
        Route::get('/login', 'login')->name('tenant.login');
        Route::post('/otp/generate', 'generate')->name('tenant.otp.generate');
        //Route::get('/otp/verification/{user_id}', 'verification')->name('tenant.otp.verification');
        Route::get('/otp/verification/{user_id}/{email}', 'generateVerification')->name('tenant.otp.verification');
        Route::post('/otp/login', 'loginWithOtp')->name('tenant.otp.getlogin');
        Route::post('/logout', 'logout')->name('tenant.logout');
    });

    Route::resource('users', App\Http\Controllers\App\UserController::class)->middleware('auth')->names('tenant.users');

    //Route::get('/home', [App\Http\Controllers\App\HomeController::class, 'index'])->name('home');
    Route::middleware(['auth'])->controller(App\Http\Controllers\App\UnitController::class)->name('tenant.unit.')->group(function () {
        Route::get('unit', 'index')->name('index');
        Route::get('unit-show', 'show')->name('show');
        Route::post('store-unit', 'store')->name('store');
        Route::post('edit-unit-status', 'editStatus')->name('edit-status');
        Route::post('delete-unit', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LeadGroupController::class)->name('tenant.lead-groups.')->group(function () {
        Route::get('lead-groups', 'index')->name('index');
        Route::get('lead-groups-show', 'show')->name('show');
        Route::post('store-lead-groups', 'store')->name('store');
        Route::post('edit-lead-groups-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-groups', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\CustomerCategoryController::class)->name('tenant.customer-category.')->group(function () {
        Route::get('customer-category', 'index')->name('index');
        Route::get('customer-category-show', 'show')->name('show');
        Route::post('store-customer-category', 'store')->name('store');
        Route::post('edit-customer-category-status', 'editStatus')->name('edit-status');
        Route::post('delete-customer-category', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\CustomerLeadController::class)->name('tenant.customer-lead.')->group(function () {
        Route::get('lead-source', 'index')->name('index');
        Route::get('lead-source-show', 'show')->name('show');
        Route::post('store-lead-source', 'store')->name('store');
        Route::post('edit-lead-source-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-source', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\TaxController::class)->name('tenant.tax.')->group(function () {
        Route::get('tax', 'index')->name('index');
        Route::get('tax-show', 'show')->name('show');
        Route::post('store-tax', 'store')->name('store');
        Route::post('edit-tax-status', 'editStatus')->name('edit-status');
        Route::post('delete-tax', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LostReasonController::class)->name('tenant.lost.reason.')->prefix('lost/')->group(function () {
        Route::get('reason', 'index')->name('index');
        Route::get('reason-show', 'show')->name('show');
        Route::post('store-reason', 'store')->name('store');
        Route::post('edit-reason-status', 'editStatus')->name('edit-status');
        Route::post('delete-reason', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LeadStageController::class)->name('tenant.lead.stage.')->prefix('lead/')->group(function () {
        Route::get('stage', 'index')->name('index');
        Route::get('stage-show', 'show')->name('show');
        Route::post('store-stage', 'store')->name('store');
        Route::post('edit-stage-status', 'editStatus')->name('edit-status');
        Route::post('delete-stage', 'destroy')->name('delete');
        Route::post('sortable-stage', 'sortableLeadStage')->name('sortable');
    });

    // Route::get('lead', [\App\Http\Controllers\App\CustomerController::class, 'index'])->name('tenant.customer.index');

    Route::controller(\App\Http\Controllers\App\SettingController::class)->name('tenant.settings.')->prefix('settings/')->group(function () { //middleware(['permissionCheck:unit_view'])->
        Route::get('general', 'index')->name('general.index');
        // Route::post('follow-up-notes-flag/update', 'followUpNotesFlagUpdate')->name('follow-up-notes-flag.index');
        // Route::post('indiamart-flag/update', 'indiamartFlagUpdate')->name('indiamart-flag.index');
        // Route::post('facebook-flag/update', 'facebookFlagUpdate')->name('facebook-flag.index');
        // Route::post('tradeindia-flag/update', 'tradeindiaFlagUpdate')->name('tradeindia-flag.index');
        // Route::post('lead-merge-flag/update', 'leadMergeFlagUpdate')->name('lead-merge-flag.index');
    });
});
// Route::group([
//         'prefix' => '/{tenant}', 
//         'middleware' => [
//             'web',
//             //InitializeTenancyByDomain::class,
//             InitializeTenancyByPath::class,
//             //PreventAccessFromCentralDomains::class,
//         ],
//     ], function () {
//     Route::group(['middleware' => 'web'], function () { //dd(tenant('id'));
//         Route::controller(\App\Http\Controllers\App\AuthOtpController::class)->group(function () {
//             Route::get('/login', 'login')->name('tenant.otp.login');
//             Route::post('/otp/generate', 'generate')->name('otp.generate');
//             Route::get('/otp/verification/{user_id}', 'verification')->name('otp.verification');
//             Route::post('/otp/login', 'loginWithOtp')->name('otp.getlogin');
//             Route::post('/logout', 'logout')->name('logout');
//         });
//     });
//     //   Auth::routes();




//     Route::controller(\App\Http\Controllers\App\CustomerLeadController::class)->name('customer-lead.')->group(function () {
//         Route::get('lead-source', 'index')->name('index');
//         Route::get('lead-source-show', 'show')->name('show');
//         Route::post('store-lead-source', 'store')->name('store');
//         Route::post('edit-lead-source-status', 'editStatus')->name('edit-status');
//         Route::post('delete-lead-source', 'destroy')->name('delete');
//     })->middleware('auth');

// });
