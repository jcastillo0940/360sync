<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanOldFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $directory;
    protected $days;

    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct($directory = 'temp', $days = 7)
    {
        $this->directory = $directory;
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $files = Storage::files($this->directory);
        $deleted = 0;
        $cutoffDate = Carbon::now()->subDays($this->days);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));

            if ($lastModified->lt($cutoffDate)) {
                Storage::delete($file);
                $deleted++;
            }
        }

        \Log::info("Limpieza de archivos completada", [
            'directory' => $this->directory,
            'deleted' => $deleted,
            'older_than_days' => $this->days
        ]);
    }
}