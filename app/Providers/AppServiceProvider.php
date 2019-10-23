<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // read options file
        if (Storage::exists('options.json'))
        {
            // decode from json
            $options = json_decode(Storage::get('options.json'), true);

            // set config from options
            config($options);

            // set is installed config
            config(['app.is_installed' => Storage::exists('installed')]);

            // if cookie consent is being displayed
            if ($options['app.display_cookie_consent'])
            {
                // cookie consent result
                $cookie_consent = Cookie::get('converse_cookie_consent', false);

                // set has cookie consent config
                config(['user.has_cookie_consent' => is_string($cookie_consent) && $cookie_consent === 'true']);
            }
        }

        // view composer for app
        view()->composer('layouts.app', function ($view) {
            // header menus
            $menus = \App\Menu::orderBy('order', 'asc')->get();

            // new messages count
            $messages = (auth()->check()) ? \App\Message::where('receiver_id', auth()->user()->id)->where('is_seen', false)->count() : 0;

            $view
                ->with('menus', $menus)
                ->with('messages', $messages);
        });
    }
}
