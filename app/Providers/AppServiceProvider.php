<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Fix para índices en MySQL < 5.7.7 y MariaDB < 10.2.2
         * 
         * Razón: utf8mb4 usa 4 bytes por carácter.
         * MySQL antiguo tiene límite de 767 bytes para índices.
         * 191 caracteres × 4 bytes = 764 bytes (bajo el límite)
         * 
         * 191 caracteres es más que suficiente para:
         * - Emails (RFC 5321: máx 254 caracteres)
         * - Usernames
         * - Slugs
         * 
         * Mantiene índices únicos para buenas prácticas de BD.
         */
        Schema::defaultStringLength(191);
    }
}