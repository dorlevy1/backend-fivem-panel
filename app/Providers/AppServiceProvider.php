<?php

namespace App\Providers;

use App\Providers\SimpleHasher;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Broadcast::routes();
        require base_path('routes/web.php');

        Auth::provider('discordAuth', function (Application $app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new DiscordProvider(new SimpleHasher(), $config['model']);
        });
    }
}
