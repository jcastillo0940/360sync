<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportLog;

class ImportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importType;
    protected $userId;

    public $tries = 1;
    public $timeout = 600; // 10 minutos

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $importType, $userId)
    {
        $this->filePath = $filePath;
        $this->importType = $importType;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = ImportLog::create([
            'user_id' => $this->userId,
            'type' => $this->importType,
            'status' => 'processing',
            'started_at' => now()
        ]);

        try {
            $content = Storage::get($this->filePath);
            $rows = $this->parseCSV($content);

            $successful = 0;
            $failed = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    $this->importRow($row);
                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $log->update([
                'status' => 'completed',
                'records_processed' => count($rows),
                'records_successful' => $successful,
                'records_failed' => $failed,
                'errors' => $errors,
                'completed_at' => now()
            ]);

            // Notificar al usuario
            SendEmailJob::dispatch(
                $log->user->email,
                'Importación completada',
                'emails.import-completed',
                ['log' => $log]
            );

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            // Limpiar archivo temporal
            Storage::delete($this->filePath);
        }
    }

    /**
     * Parsear contenido CSV
     */
    protected function parseCSV($content): array
    {
        $rows = [];
        $lines = explode("\n", $content);
        $headers = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            if (trim($line)) {
                $data = str_getcsv($line);
                $rows[] = array_combine($headers, $data);
            }
        }

        return $rows;
    }

    /**
     * Importar una fila según el tipo
     */
    protected function importRow(array $row): void
    {
        switch ($this->importType) {
            case 'users':
                \App\Models\User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password'] ?? 'default123')
                ]);
                break;

            case 'products':
                \App\Models\Product::create([
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'stock' => $row['stock'] ?? 0
                ]);
                break;

            // Agregar más tipos según necesidad
        }
    }

    /**
     * Manejar el fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Error en importación: ' . $exception->getMessage(), [
            'file' => $this->filePath,
            'type' => $this->importType
        ]);
    }
}