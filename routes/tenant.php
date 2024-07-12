<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
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

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });

    Route::group(['middleware' => 'web'], function () {
        Route::controller(\App\Http\Controllers\App\AuthOtpController::class)->group(function () {
            Route::get('/login', 'login')->name('otp.login');
            Route::post('/otp/generate', 'generate')->name('otp.generate');
            Route::get('/otp/verification/{user_id}', 'verification')->name('otp.verification');
            Route::post('/otp/login', 'loginWithOtp')->name('otp.getlogin');
            Route::post('/logout', 'logout')->name('logout');
        });


        // Login Routes
//        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
       /* Route::post('/login', [LoginController::class, 'login']);
       Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // Registration Routes
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

        Route::get('/password/reset', [ForgotPasswordController::class,'showLinkRequestForm'])->name('password.request');
        Route::get('/password/reset/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');*/

//        Route::post('/register', [RegisterController::class, 'register']);


    });
//   Auth::routes();
    Route::get('/home', [App\Http\Controllers\App\HomeController::class, 'index'])->name('home');
    Route::resource('users', App\Http\Controllers\App\UserController::class)->middleware('auth');
    Route::middleware(['auth'])->controller(App\Http\Controllers\App\UnitController::class)->name('unit.')->group(function () {
        Route::get('unit', 'index')->name('index');
        Route::get('unit-show', 'show')->name('show');
        Route::post('store-unit', 'store')->name('store');
        Route::post('edit-unit-status', 'editStatus')->name('edit-status');
        Route::post('delete-unit', 'destroy')->name('delete');
    });

    Route::controller(\App\Http\Controllers\App\LeadGroupController::class)->name('lead-groups.')->group(function () {
        Route::get('lead-groups', 'index')->name('index');
        Route::get('lead-groups-show', 'show')->name('show');
        Route::post('store-lead-groups', 'store')->name('store');
        Route::post('edit-lead-groups-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-groups', 'destroy')->name('delete');
    })->middleware('auth');

    Route::controller(\App\Http\Controllers\App\CustomerCategoryController::class)->name('customer-category.')->group(function () {
        Route::get('customer-category', 'index')->name('index');
        Route::get('customer-category-show', 'show')->name('show');
        Route::post('store-customer-category', 'store')->name('store');
        Route::post('edit-customer-category-status', 'editStatus')->name('edit-status');
        Route::post('delete-customer-category', 'destroy')->name('delete');
    })->middleware('auth');

    Route::controller(\App\Http\Controllers\App\CustomerLeadController::class)->name('customer-lead.')->group(function () {
        Route::get('lead-source', 'index')->name('index');
        Route::get('lead-source-show', 'show')->name('show');
        Route::post('store-lead-source', 'store')->name('store');
        Route::post('edit-lead-source-status', 'editStatus')->name('edit-status');
        Route::post('delete-lead-source', 'destroy')->name('delete');
    })->middleware('auth');

    Route::controller(\App\Http\Controllers\App\TaxController::class)->name('tax.')->group(function () {
        Route::get('tax', 'index')->name('index');
        Route::get('tax-show', 'show')->name('show');
        Route::post('store-tax', 'store')->name('store');
        Route::post('edit-tax-status', 'editStatus')->name('edit-status');
        Route::post('delete-tax', 'destroy')->name('delete');
    })->middleware('auth');

    Route::controller(\App\Http\Controllers\App\LostReasonController::class)->name('lost.reason.')->prefix('lost/')->group(function () {
        Route::get('reason', 'index')->name('index');
        Route::get('reason-show', 'show')->name('show');
        Route::post('store-reason', 'store')->name('store');
        Route::post('edit-reason-status', 'editStatus')->name('edit-status');
        Route::post('delete-reason', 'destroy')->name('delete');
    })->middleware('auth');

    Route::controller(\App\Http\Controllers\App\LeadStageController::class)->name('lead.stage.')->prefix('lead/')->group(function () {
        Route::get('stage', 'index')->name('index');
        Route::get('stage-show', 'show')->name('show');
        Route::post('store-stage', 'store')->name('store');
        Route::post('edit-stage-status', 'editStatus')->name('edit-status');
        Route::post('delete-stage', 'destroy')->name('delete');
        Route::post('sortable-stage', 'sortableLeadStage')->name('sortable');
    })->middleware('auth');


});
