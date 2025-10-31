<?php

namespace App\Listeners;

use App\Events\ApiConnectionFailedEvent;
use App\Mail\ApiConnectionErrorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendApiConnectionErrorListener
{
    public function __construct()
    {
        //
    }

    public function handle(ApiConnectionFailedEvent $event): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');

            Mail::to($adminEmail)->send(
                new ApiConnectionErrorEmail(
                    $event->apiName,
                    $event->endpoint,
                    $event->errorMessage,
                    $event->statusCode,
                    $event->retryAttempts
                )
            );

            Log::info('Email de error de conexión API enviado', [
                'api_name' => $event->apiName,
                'endpoint' => $event->endpoint,
                'email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de error de API: ' . $e->getMessage());
        }
    }
}