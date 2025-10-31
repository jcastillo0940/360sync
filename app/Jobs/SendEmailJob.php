<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericEmail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $subject;
    protected $view;
    protected $data;

    /**
     * Número de intentos del job
     */
    public $tries = 3;

    /**
     * Tiempo de espera antes de reintentar (segundos)
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($to, $subject, $view, $data = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->to)->send(
            new GenericEmail($this->subject, $this->view, $this->data)
        );
    }

    /**
     * Manejar el fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        // Log del error
        \Log::error('Error enviando email: ' . $exception->getMessage(), [
            'to' => $this->to,
            'subject' => $this->subject
        ]);
    }
}