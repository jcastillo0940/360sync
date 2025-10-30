<?php

namespace App\Exceptions;

use Exception;

class DataValidationException extends Exception
{
    protected $errors;
    protected $entityType;
    protected $entityId;

    public function __construct($message = "", $errors = [], $entityType = null, $entityId = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->errors = $errors;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function report()
    {
        \Log::warning('Data Validation Exception: ' . $this->getMessage(), [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'errors' => $this->errors,
        ]);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'message' => $this->getMessage(),
                'entity_type' => $this->entityType,
                'entity_id' => $this->entityId,
                'validation_errors' => $this->errors,
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $this->getMessage())
            ->withErrors($this->errors);
    }
}