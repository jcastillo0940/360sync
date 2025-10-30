<?php

namespace App\Exceptions;

use Exception;

class SyncException extends Exception
{
    protected $syncType;
    protected $details;

    public function __construct($message = "", $syncType = null, $details = [], $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->syncType = $syncType;
        $this->details = $details;
    }

    public function getSyncType()
    {
        return $this->syncType;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function report()
    {
        \Log::error('Sync Exception: ' . $this->getMessage(), [
            'sync_type' => $this->syncType,
            'details' => $this->details,
            'trace' => $this->getTraceAsString(),
        ]);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'message' => $this->getMessage(),
                'sync_type' => $this->syncType,
                'details' => $this->details,
            ], 500);
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}