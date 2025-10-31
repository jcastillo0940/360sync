<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Asegurar que el email esté en minúsculas
        if (!empty($user->email)) {
            $user->email = strtolower($user->email);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('Nuevo usuario creado', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        // Enviar email de bienvenida
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Exception $e) {
            Log::error('Error enviando email de bienvenida: ' . $e->getMessage());
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Log::info('Usuario actualizado', [
            'id' => $user->id,
            'changes' => $user->getChanges(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::warning('Usuario eliminado', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('Usuario restaurado', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::warning('Usuario eliminado permanentemente', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}