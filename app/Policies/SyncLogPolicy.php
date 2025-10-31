<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SyncLog;

class SyncLogPolicy
{
    /**
     * Determine if the user can view any sync logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_sync_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can view the sync log.
     */
    public function view(User $user, SyncLog $syncLog): bool
    {
        return $user->hasPermission('view_sync_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can create sync logs.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_sync_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can update the sync log.
     */
    public function update(User $user, SyncLog $syncLog): bool
    {
        return $user->hasPermission('edit_sync_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the sync log.
     */
    public function delete(User $user, SyncLog $syncLog): bool
    {
        return $user->hasPermission('delete_sync_logs') || $user->isAdmin();
    }

    /**
     * Determine if the user can restore the sync log.
     */
    public function restore(User $user, SyncLog $syncLog): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the sync log.
     */
    public function forceDelete(User $user, SyncLog $syncLog): bool
    {
        return $user->isAdmin();
    }
}