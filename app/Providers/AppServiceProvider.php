<?php

namespace App\Providers;

use App\Broadcasting\DiscordChannel;
use App\Feature;
use App\Helpers\API;
use App\Helpers\Discord\ClientService;
use App\Helpers\Discord\DiscordMessage;
use App\Helpers\Discord\Features\JoinToGang;
use App\Helpers\Discord\Features\RedeemCode;
use App\Helpers\Discord\Interaction;
use App\Message;
use App\RealSms;
use App\Services\DiscordService;
use App\SmsInterface;
use Discord\Discord as DiscordPHP;
use Discord\WebSockets\Intents;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsInterface::class, RealSms::class);
        $this->app->bind(JoinToGang::class, Feature::class);
        $this->app->bind(Interaction::class, DiscordMessage::class);
        $this->app->bind(RedeemCode::class, Feature::class);
        $this->app->singleton(ClientService::class, function ($app) {
            return new ClientService();
        });

        $this->app->singleton(API::class, function ($app) {
            return new API();
        });

        $this->app->singleton(DiscordPHP::class, function ($app) {
            return new DiscordPHP([
                'token' => env('DISCORD_BOT_TOKEN'),
                'loadAllMembers' => true,
                'storeMessages' => true,
                'intents' => Intents::getDefaultIntents() | Intents::GUILDS |
                    Intents::GUILD_MESSAGES |
                    Intents::DIRECT_MESSAGES |
                    Intents::GUILD_INVITES |
                    Intents::GUILD_MEMBERS |
                    Intents::GUILD_MESSAGE_REACTIONS |
                    Intents::MESSAGE_CONTENT
            ]);
        });

        $this->app->bind(JoinToGang::class, function ($app) {
            return new JoinToGang($app->make(API::class), $app->make(DiscordPHP::class), $app->make(DiscordService::class));
        });

        $this->app->bind(\App\Helpers\Discord\Discord::class, function ($app) {
            return new \App\Helpers\Discord\Discord($app->make(DiscordPHP::class));
        });
        $this->app->bind(DiscordMessage::class, function ($app) {
            return new \App\Helpers\Discord\Discord($app->make(DiscordPHP::class));
        });
        $this->app->bind(DiscordMessage::class, function ($app) {
            return new DiscordMessage($app->make(Message::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Broadcast::routes();
        require base_path('routes/web.php');

        Notification::extend('discord', function ($app) {
            return new DiscordChannel();
        });

        Auth::provider('discordAuth', function (Application $app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new DiscordProvider(new SimpleHasher(), $config['model']);
        });


    }
}
