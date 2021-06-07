<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      // if(App::enviroment("production")){
        URL::forceScheme(request()->secure() ? "https" : "http");
      // }
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
