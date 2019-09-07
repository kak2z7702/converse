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
            // header menus
            $menus = \App\Menu::orderBy('order', 'asc')->get();

            $view->with('menus', $menus)->with('is_installed', Storage::exists('installed'));
        });
    }
}
