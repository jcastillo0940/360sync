<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_users') || $user->isAdmin();
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Los usuarios pueden ver su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermission('view_users') || $user->isAdmin();
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_users') || $user->isAdmin();
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Los usuarios pueden actualizar su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermission('edit_users') || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // No se puede eliminar a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermission('delete_users') || $user->isAdmin();
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can change roles.
     */
    public function changeRole(User $user, User $model): bool
    {
        // No se puede cambiar su propio rol
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }
}