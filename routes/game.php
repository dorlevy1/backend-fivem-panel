<?php

use App\Events\DatabaseChange;
use App\Http\Controllers\Admin\PlayersController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DiscordController;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


Route::post('/playerConnecting', [PlayersController::class, 'playerConnecting']);
Route::post('/playerDisconnect', [PlayersController::class, 'playerDisconnect']);
Route::post('/checkForPermissions', [AdminController::class, 'checkForPermissions']);
