<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.env') == "production") {
            //dd(realpath(base_path('../public_html/dm11')));
            $this->app->bind('path.public', function () {
                return base_path('../public_html/dm11');
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        Schema::defaultStringLength(191);

        // URL::forceScheme(request()->secure() ? "https" : "http");
        if(config('app.env') !== "local"){
            URL::forceScheme("https");
        }
        Password::defaults(function () {
            $rule = Password::min(8)
                // ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return $rule;
        });
    }
}
