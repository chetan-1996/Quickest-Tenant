<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function (){
            $centralDomains = config('tenancy.central_domains');
            foreach ($centralDomains as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }
            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
            Route::middleware('api')
                ->group(base_path('routes/api.php'));
        },

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'initializeTenancyManually' => \App\Http\Middleware\InitializeTenancyManually::class,
            'tenant' => \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
            'LogActivity' => App\Helpers\LogActivity::class,
            'Excel' => Maatwebsite\Excel\Facades\Excel::class,
            // 'PermissionCheck' => App\Helpers\PermissionCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
//web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php',
