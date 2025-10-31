<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\SyncLog;
use App\Models\User;
use App\Models\ApiLog;
use App\Policies\SyncLogPolicy;
use App\Policies\UserPolicy;
use App\Policies\ApiLogPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SyncLog::class => SyncLogPolicy::class,
        User::class => UserPolicy::class,
        ApiLog::class => ApiLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Definir gates personalizados si es necesario
        Gate::define('manage-sync', function (User $user) {
            return $user->hasPermission('manage_sync') || $user->isAdmin();
        });

        Gate::define('view-dashboard', function (User $user) {
            return $user->hasPermission('view_dashboard') || $user->isAdmin();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isAdmin();
        });
    }
}