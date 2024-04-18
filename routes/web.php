<?php

use App\Events\DatabaseChange;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DiscordController;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


Route::get('test', function () {
    $notify = new DatabaseChange('checkBinKa', 'my-event');
    $notify->setData([
        'type' => 'ANNOUNCE',
        'message' => 'This is message from backserver panel localhost',
        'timeout' => 10000
    ]);
    $notify->send($notify);
    //    dd(event(new DatabaseChange('hello world')));
    dd($notify->send($notify));
});


Route::get('/', function (Request $request) {
    return Player::all();
})->middleware(['auth:api']);


Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . config('discord.client_id')
    . '&redirect_uri=' . config('discord.redirect_uri')
    . '&response_type=code&scope=' . implode('%20', explode('+', config('discord.scopes')))
    . '&prompt=' . config('config.prompt', 'none'))
    ->name('login');


Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('discord.refresh_token');
