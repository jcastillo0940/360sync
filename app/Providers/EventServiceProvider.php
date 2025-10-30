<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\SyncStartedEvent;
use App\Events\SyncCompletedEvent;
use App\Events\SyncFailedEvent;
use App\Events\DataConflictDetectedEvent;
use App\Events\ApiConnectionFailedEvent;
use App\Listeners\LogSyncStartedListener;
use App\Listeners\LogSyncCompletedListener;
use App\Listeners\SendSyncReportListener;
use App\Listeners\LogSyncFailedListener;
use App\Listeners\SendSyncErrorListener;
use App\Listeners\SendDataConflictListener;
use App\Listeners\SendApiConnectionErrorListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SyncStartedEvent::class => [
            LogSyncStartedListener::class,
        ],
        SyncCompletedEvent::class => [
            LogSyncCompletedListener::class,
            SendSyncReportListener::class,
        ],
        SyncFailedEvent::class => [
            LogSyncFailedListener::class,
            SendSyncErrorListener::class,
        ],
        DataConflictDetectedEvent::class => [
            SendDataConflictListener::class,
        ],
        ApiConnectionFailedEvent::class => [
            SendApiConnectionErrorListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}