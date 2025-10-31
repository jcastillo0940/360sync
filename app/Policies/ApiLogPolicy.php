<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApiLog;

class ApiLogPolicy
{
    /**
     * Determine if the user can view any api logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_api_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can view the api log.
     */
    public function view(User $user, ApiLog $apiLog): bool
    {
        return $user->hasPermission('view_api_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can create api logs.
     */
    public function create(User $user): bool
    {
        // Normalmente los logs se crean automáticamente
        return true;
    }

    /**
     * Determine if the user can update the api log.
     */
    public function update(User $user, ApiLog $apiLog): bool
    {
        // Los logs generalmente no se editan
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the api log.
     */
    public function delete(User $user, ApiLog $apiLog): bool
    {
        return $user->hasPermission('delete_api_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can restore the api log.
     */
    public function restore(User $user, ApiLog $apiLog): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the api log.
     */
    public function forceDelete(User $user, ApiLog $apiLog): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can export api logs.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('export_api_logs') || $user->isAdmin();
    }
}