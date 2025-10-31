<?php

namespace App\Exceptions;

use Exception;

class ApiConnectionException extends Exception
{
    protected $apiName;
    protected $endpoint;
    protected $statusCode;
    protected $responseBody;

    public function __construct($message = "", $apiName = null, $endpoint = null, $statusCode = null, $responseBody = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->apiName = $apiName;
        $this->endpoint = $endpoint;
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function getApiName()
    {
        return $this->apiName;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function report()
    {
        \Log::error('API Connection Exception: ' . $this->getMessage(), [
            'api_name' => $this->apiName,
            'endpoint' => $this->endpoint,
            'status_code' => $this->statusCode,
            'response_body' => $this->responseBody,
            'trace' => $this->getTraceAsString(),
        ]);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'message' => $this->getMessage(),
                'api_name' => $this->apiName,
                'endpoint' => $this->endpoint,
                'status_code' => $this->statusCode,
            ], $this->statusCode ?? 500);
        }

        return redirect()->back()->with('error', 'Error de conexión con ' . $this->apiName . ': ' . $this->getMessage());
    }
}