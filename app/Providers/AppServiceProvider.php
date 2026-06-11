<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. ACESSO TOTAL (Apenas Admin e Patrão podem eliminar dados ou ver utilizadores)
        Gate::define('acesso-total', function ($user) {
            return in_array($user->role, ['admin', 'patrao']);
        });

        // 2. PERMISSÃO OPERACIONAL (Admin, Patrão e Staff podem gerir mesas e pedidos)
        Gate::define('operar-restaurante', function ($user) {
            return in_array($user->role, ['admin', 'patrao', 'staff']);
        });

        // 3. APENAS ADMIN (Se quiseres algo exclusivo para o Admin default no futuro)
        Gate::define('apenas-admin', function ($user) {
            return $user->role === 'admin';
        });
    }
}