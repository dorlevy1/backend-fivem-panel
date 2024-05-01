<?php

declare(strict_types=1);

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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
    Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . config('discord.client_id')
        . '&redirect_uri=' . config('discord.redirect_uri')
        . '&response_type=code&scope=' . implode('%20', explode('+', config('discord.scopes')))
        . '&prompt=' . config('config.prompt', 'none'))
         ->name('login');


    Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('discord.refresh_token');

});
