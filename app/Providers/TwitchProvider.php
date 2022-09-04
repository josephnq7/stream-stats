<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use romanzipp\Twitch\Twitch;

class TwitchProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Twitch::class, function ($app) {
            $twitch = new Twitch();
            $twitch->setClientId(config('services.twitch.client_id'));
            $twitch->setRedirectUri(config('services.twitch.redirect'));
            $twitch->setClientSecret(config('services.twitch.client_secret'));
            return $twitch;
        });
    }
}
