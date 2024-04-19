<?php

use App\Http\Controllers\Admin\BansController;
use App\Http\Controllers\Admin\GangsController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PlayersController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WarnsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;


Route::get('/auth', [AuthController::class, 'login']);
Route::post('auth', [AuthController::class, 'login']);
Route::post('/checkAuth', [AuthController::class, 'checkAuth']);
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');

Route::post('/broadcasting/auth', function () {
    return ['dor'];

    return auth()->check() ? auth()->user() : abort(403);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('/checkPermissions', [AdminController::class, 'checkForPermissions']);

    Route::post('/discord/players', [DiscordController::class, 'getOnlinePlayers']);
    Route::post('/discord/checkOnline', [DiscordController::class, 'checkForOnlinePlayer']);
    Route::post('/discord/playerData', [DiscordController::class, 'getPlayerData']);


    Route::prefix('players')->middleware(['auth:api'])->group(function () {
        Route::post('/get', [PlayersController::class, 'view']);
        Route::post('/newPlayers', [PlayersController::class, 'getJoinedPlayers']);
        Route::post('/{citizenid}/inventory', [PlayersController::class, 'getInventory']);
        Route::post('/{citizenid}', [PlayersController::class, 'getPlayer']);
        Route::post('/update', [PlayersController::class, 'update']);
        Route::delete('/delete', [PlayersController::class, 'delete']);
    });

    Route::prefix('permissions')->group(function () {
        Route::post('/get', [PermissionsController::class, 'get']);
        Route::post('/add', [PermissionsController::class, 'addPlayer']);
        Route::post('/update', [PermissionsController::class, 'update']);
        Route::delete('/delete', [PermissionsController::class, 'delete']);
    });

    Route::prefix('gangs')->group(function () {
        Route::post('/', [GangsController::class, 'view']);
    });
    Route::prefix('warns')->group(function () {
        Route::post('/', [WarnsController::class, 'view']);
        Route::post('/add', [WarnsController::class, 'add']);
        Route::post('/update', [WarnsController::class, 'update']);
        Route::post('/remove', [WarnsController::class, 'delete']);
    });

    Route::prefix('admins_action')->group(function () {
        Route::post('/', [AdminController::class, 'view']);
    });

    Route::prefix('bans')->group(function () {
        Route::post('/', [BansController::class, 'view']);
        Route::post('/add', [BansController::class, 'add']);
        Route::post('/update', [BansController::class, 'update']);
        Route::post('/remove', [BansController::class, 'delete']);
    });

    Route::prefix('reports')->group(function () {
        Route::post('/get', [ReportController::class, 'view']);
        Route::post('/add', [ReportController::class, 'add']);
        Route::post('/update', [ReportController::class, 'update']);
        Route::post('/claim', [ReportController::class, 'claim']);
        Route::post('/remove', [ReportController::class, 'delete']);
    });
});