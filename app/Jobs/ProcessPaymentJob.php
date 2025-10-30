<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Services\PaymentService;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $paymentData;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, array $paymentData)
    {
        $this->orderId = $orderId;
        $this->paymentData = $paymentData;
    }

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService): void
    {
        $order = Order::findOrFail($this->orderId);

        try {
            $result = $paymentService->processPayment($order, $this->paymentData);

            if ($result['success']) {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $result['transaction_id']
                ]);

                // Despachar job de notificación
                SendEmailJob::dispatch(
                    $order->customer_email,
                    'Pago confirmado',
                    'emails.payment-confirmed',
                    ['order' => $order]
                );
            }
        } catch (\Exception $e) {
            $order->update(['payment_status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Manejar el fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        $order = Order::find($this->orderId);
        
        if ($order) {
            $order->update(['payment_status' => 'failed']);
        }

        \Log::error('Error procesando pago: ' . $exception->getMessage(), [
            'order_id' => $this->orderId
        ]);
    }
}