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
        // application options
        $options = null;

        // read options file
        if (Storage::exists('options.json'))
        {
            // decode from json
            $options = json_decode(Storage::get('options.json'));

            // set app name
            config(['app.name' => $options->community->name]);
        }

        // view composer for all views
        view()->composer('*', function ($view) use ($options) {
            $view->with('options', $options);
        });

        // view composer for app
        view()->composer('layouts.app', function ($view) {
            // header menus
            $menus = \App\Menu::orderBy('order', 'asc')->get();

            // new messages count
            $messages = (auth()->check()) ? \App\Message::where('receiver_id', auth()->user()->id)->where('is_seen', false)->count() : 0;

            // cookie consent result
            $cookie_consent = Cookie::get('converse_cookie_consent', false);

            // convert to boolean
            if (is_string($cookie_consent) && $cookie_consent === 'true')
                $cookie_consent = true;

            $view
                ->with('menus', $menus)
                ->with('messages', $messages)
                ->with('is_installed', Storage::exists('installed'))
                ->with('has_cookie_consent', $cookie_consent);
        });
    }
}
