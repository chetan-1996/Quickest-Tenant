<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return 'This is your multi-tenant application. The id of the current tenant is';
// });
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::prefix('api/v7')->group(function () {
    require __DIR__.'/../app/Http/Controllers/API/V7/routes.php';
});
