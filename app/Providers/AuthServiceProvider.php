<?php

namespace App\Providers;

use App\Models\ListaMoraItem;
use App\Models\SolicitudAtencionExterna;
use Spatie\Permission\Models\Role;
use App\Policies\RolePolicy;
use App\Policies\ListaMoraPolicy;
use App\Policies\SolicitudAtencionExternaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
        Role::class => RolePolicy::class,
        ListaMoraItem::class => ListaMoraPolicy::class,
        SolicitudAtencionExterna::class => SolicitudAtencionExternaPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user) {
          return $user->estado == 1;
        });

        Gate::after(function ($user) {
          return $user->hasRole("super user");
        });
    }
}
