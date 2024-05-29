<?php

use App\Events\DatabaseChange;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\SubdomainEnsure;
use Discord\Discord;
use Discord\WebSockets\Intents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function (Request $request) {
    return \App\Models\GangCreationRequest::all();
});

Route::get('dis', function () {

});

Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . config('discord.client_id')
    . '&redirect_uri=' . config('discord.redirect_uri')
    . '&response_type=code&scope=' . implode('%20', explode('+', config('discord.scopes')))
    . '&prompt=' . config('config.prompt', 'none'))
     ->name('login');


Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('discord.refresh_token');
