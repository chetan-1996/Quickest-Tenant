<?php

use App\Http\Controllers\API\V7\{
    AuthController,
    // CompanyCategoryController,
    // CustomerController,
    // EstimateController,
    // ItemController,
    // ProductController,
    // ProposalController,
    // RegionController,
    TermConditionController
};
//use App\Http\Controllers\ExpirePlanController;
use App\Mail\ExtendedMail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is';
    });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
//API route for login user
Route::middleware(['tenant'])->group(function(){
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('{tenant}/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/verify-otp-register', [AuthController::class, 'verifyOtpRegister']);
    //Route::get('/business-category', [CompanyCategoryController::class, '__invoke']);
});


Route::group(['middleware' => ['auth:sanctum', 'throttle:none']], function () { //, 'throttle:540,1'
    // API route for logout user
    Route::post('/logout', [AuthController::class, 'logout']);
});