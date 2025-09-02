<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Orcamento; // Adicione esta linha no topo
use App\Policies\OrcamentoPolicy; // Adicione esta linha no topo

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define a regra 'acessar-admin'
        // Esta função será chamada toda vez que o middleware 'can:acessar-admin' for usado.
        Gate::define('acessar-admin', function (User $user) {
            // Ela retorna 'true' se o usuário logado tiver a permissão 'acessar-admin',
            // que nós demos ao perfil 'Super Admin'.
            return $user->hasPermissionTo('acessar-admin');
        });
    }
}