<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'api_name' => $this->api_name,
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'status_code' => $this->status_code,
            'request_data' => $this->when($request->user()?->isAdmin(), $this->request_data),
            'response_data' => $this->when($request->user()?->isAdmin(), $this->response_data),
            'error_message' => $this->error_message,
            'response_time' => $this->response_time . ' ms',
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'user' => new UserResource($this->whenLoaded('user')),
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}