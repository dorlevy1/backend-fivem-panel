<?php

use App\Events\DatabaseChange;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\SubdomainEnsure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::domain('{subdomain}.' . env('APP_URL'))->middleware([SubdomainEnsure::class])->group(function () {
    Route::get('test', function ($subdomain) {
        dd($subdomain);
        $this->inGameNotify = new DatabaseChange('inGame.215978', 'my-event');

        $this->inGameNotify->setData([
            'type'    => 'KICK',
            'message' => 'test',
            'timeout' => 10000,
        ]);
        dd($this->inGameNotify->send($this->inGameNotify));
    });
});

Route::get('/test', function (Request $request) {
    $helper = new \App\Helpers\Discord\DiscordAPI();
//    $helper->createWebhookChannels();
});


Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . config('discord.client_id')
    . '&redirect_uri=' . config('discord.redirect_uri')
    . '&response_type=code&scope=' . implode('%20', explode('+', config('discord.scopes')))
    . '&prompt=' . config('config.prompt', 'none'))
     ->name('login');


Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('discord.refresh_token');
