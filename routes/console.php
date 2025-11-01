<?php
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronizar SKUs de Magento - 3 veces al día: 1 AM, 10 AM, 5 PM
Schedule::command('magento:sync-skus')
    ->cron('0 1,10,17 * * *')
    ->timezone('America/Mexico_City')
    ->runInBackground()
    ->withoutOverlapping(120)
    ->onSuccess(function () {
        \Log::info('Magento SKUs synced successfully via scheduled task');
    })
    ->onFailure(function () {
        \Log::error('Failed to sync Magento SKUs via scheduled task');
    });

// Sincronizar categorías de Magento - Diario a las 3:00 AM
Schedule::command('magento:sync-categories')
    ->dailyAt('03:00')
    ->timezone('America/Mexico_City')
    ->runInBackground()
    ->withoutOverlapping(60)
    ->onSuccess(function () {
        \Log::info('Magento categories synced successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to sync Magento categories');
    });

// Sincronizar conteo de productos - Diario a las 4:00 AM
Schedule::command('magento:sync-product-counts')
    ->dailyAt('04:00')
    ->timezone('America/Mexico_City')
    ->runInBackground()
    ->withoutOverlapping(60)
    ->onSuccess(function () {
        \Log::info('Product counts synced successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to sync product counts');
    });

// Limpiar logs antiguos - Diario a las 2:00 AM
Schedule::command('logs:clean')
    ->dailyAt('02:00')
    ->timezone('America/Mexico_City')
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('Old logs cleaned successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to clean old logs');
    });

// Limpiar trabajos fallidos - Semanal (Domingos 1 AM)
Schedule::command('queue:prune-failed --hours=168')
    ->weekly()
    ->sundays()
    ->at('01:00')
    ->timezone('America/Mexico_City')
    ->runInBackground();

// Limpiar batches antiguos - Semanal (Domingos 1:30 AM)
Schedule::command('queue:prune-batches --hours=168 --unfinished=72')
    ->weekly()
    ->sundays()
    ->at('01:30')
    ->timezone('America/Mexico_City')
    ->runInBackground();

// Generar reporte semanal - Lunes 8 AM
Schedule::command('report:generate weekly')
    ->weeklyOn(1, '08:00')
    ->timezone('America/Mexico_City')
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('Weekly report generated successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to generate weekly report');
    });

// Ping cada 5 minutos
Schedule::call(function () {
    \Log::info('360Sync scheduler is running at ' . now());
})->everyFiveMinutes();
