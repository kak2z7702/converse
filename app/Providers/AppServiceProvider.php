<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

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
            // community options
            $options = json_decode(Storage::get('options.json'));

            // set app name
            config(['app.name' => $options->community->name]);
        }

        // view composer for app
        view()->composer('layouts.app', function ($view) {
            $new_messages = (auth()->check()) ? \App\Message::where('receiver_id', auth()->user()->id)->where('is_seen', false)->count() : 0;
            $menus = \App\Menu::orderBy('order', 'asc')->get();

            $view->with('new_messages', $new_messages)
                ->with('menus', $menus)
                ->with('is_installed', Storage::exists('installed'));
        });
    }
}
