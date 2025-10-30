<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración General de Workflows
    |--------------------------------------------------------------------------
    */

    'enabled' => env('WORKFLOW_ENABLED', true),

    'max_concurrent_executions' => env('WORKFLOW_MAX_CONCURRENT', 5),

    'execution_timeout' => env('WORKFLOW_EXECUTION_TIMEOUT', 3600), // 1 hora

    /*
    |--------------------------------------------------------------------------
    | Configuración de Colas (Queues)
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'default' => env('WORKFLOW_QUEUE', 'default'),
        'high_priority' => env('WORKFLOW_QUEUE_HIGH', 'high'),
        'low_priority' => env('WORKFLOW_QUEUE_LOW', 'low'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Schedules (Programación)
    |--------------------------------------------------------------------------
    */

    'schedule' => [
        'enabled' => env('WORKFLOW_SCHEDULE_ENABLED', true),
        'timezone' => env('WORKFLOW_TIMEZONE', 'America/Mexico_City'),
        'overlap_prevention' => env('WORKFLOW_PREVENT_OVERLAP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Notificaciones de Workflows
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'on_start' => env('WORKFLOW_NOTIFY_START', false),
        'on_complete' => env('WORKFLOW_NOTIFY_COMPLETE', true),
        'on_error' => env('WORKFLOW_NOTIFY_ERROR', true),
        'channels' => ['mail'], // mail, slack, database
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logs de Workflows
    |--------------------------------------------------------------------------
    */

    'logs' => [
        'retention_days' => env('WORKFLOW_LOG_RETENTION', 90),
        'detailed' => env('WORKFLOW_LOG_DETAILED', true),
        'store_payload' => env('WORKFLOW_STORE_PAYLOAD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Workflow
    |--------------------------------------------------------------------------
    */

    'statuses' => [
        'pending' => 'pending',
        'running' => 'running',
        'completed' => 'completed',
        'failed' => 'failed',
        'cancelled' => 'cancelled',
        'paused' => 'paused',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Workflow Disponibles
    |--------------------------------------------------------------------------
    */

    'types' => [
        'product_sync' => [
            'name' => 'Sincronización de Productos',
            'description' => 'Sincroniza productos desde ICG a Magento',
            'class' => \App\Services\Workflows\ProductSyncWorkflow::class,
        ],
        'category_sync' => [
            'name' => 'Sincronización de Categorías',
            'description' => 'Sincroniza categorías desde ICG a Magento',
            'class' => \App\Services\Workflows\CategorySyncWorkflow::class,
        ],
        'inventory_sync' => [
            'name' => 'Sincronización de Inventario',
            'description' => 'Actualiza inventario desde ICG a Magento',
            'class' => \App\Services\Workflows\InventorySyncWorkflow::class,
        ],
        'price_sync' => [
            'name' => 'Sincronización de Precios',
            'description' => 'Actualiza precios desde ICG a Magento',
            'class' => \App\Services\Workflows\PriceSyncWorkflow::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Reintentos
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'enabled' => env('WORKFLOW_RETRY_ENABLED', true),
        'max_attempts' => env('WORKFLOW_RETRY_MAX', 3),
        'delay' => env('WORKFLOW_RETRY_DELAY', 300), // 5 minutos
    ],

];