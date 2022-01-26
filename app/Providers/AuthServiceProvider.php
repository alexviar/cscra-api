<?php

namespace App\Providers;

use App\Models\Especialidad;
use App\Models\ListaMoraItem;
use App\Models\Prestacion;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use App\Policies\EspecialidadPolicy;
use Spatie\Permission\Models\Role;
use App\Policies\RolePolicy;
use App\Policies\ListaMoraPolicy;
use App\Policies\PrestacionPolicy;
use App\Policies\SolicitudAtencionExternaPolicy;
use App\Policies\UserPolicy;
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
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        ListaMoraItem::class => ListaMoraPolicy::class,
        Especialidad::class => EspecialidadPolicy::class,
        Prestacion::class => PrestacionPolicy::class,
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
          if($user->estado !== 1){
              return false;
          }
        });

        Gate::after(function ($user) {
          if($user->isSuperUser()){
              return true;
          }
        });
    }
}
