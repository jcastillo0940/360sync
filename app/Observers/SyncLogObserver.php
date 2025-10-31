<?php

namespace App\Observers;

use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;

class SyncLogObserver
{
    /**
     * Handle the SyncLog "creating" event.
     */
    public function creating(SyncLog $syncLog): void
    {
        // Establecer valores por defecto antes de crear
        if (empty($syncLog->status)) {
            $syncLog->status = 'pending';
        }
        
        if (empty($syncLog->started_at)) {
            $syncLog->started_at = now();
        }
    }

    /**
     * Handle the SyncLog "created" event.
     */
    public function created(SyncLog $syncLog): void
    {
        Log::info('Nuevo log de sincronización creado', [
            'id' => $syncLog->id,
            'sync_type' => $syncLog->sync_type,
            'status' => $syncLog->status,
        ]);
    }

    /**
     * Handle the SyncLog "updated" event.
     */
    public function updated(SyncLog $syncLog): void
    {
        // Si cambió el estado a completed, registrar la fecha
        if ($syncLog->isDirty('status') && $syncLog->status === 'completed') {
            if (empty($syncLog->completed_at)) {
                $syncLog->completed_at = now();
                $syncLog->saveQuietly(); // Guardar sin disparar eventos
            }
        }

        Log::info('Log de sincronización actualizado', [
            'id' => $syncLog->id,
            'status' => $syncLog->status,
            'changes' => $syncLog->getChanges(),
        ]);
    }

    /**
     * Handle the SyncLog "deleted" event.
     */
    public function deleted(SyncLog $syncLog): void
    {
        Log::warning('Log de sincronización eliminado', [
            'id' => $syncLog->id,
            'sync_type' => $syncLog->sync_type,
        ]);
    }
}