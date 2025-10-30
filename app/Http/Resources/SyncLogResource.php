<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncLogResource extends JsonResource
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
            'sync_type' => $this->sync_type,
            'source' => $this->source,
            'destination' => $this->destination,
            'status' => $this->status,
            'records_processed' => $this->records_processed,
            'records_success' => $this->records_success,
            'records_failed' => $this->records_failed,
            'error_message' => $this->error_message,
            'error_details' => $this->when($this->error_details, $this->error_details),
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'duration' => $this->when($this->started_at && $this->completed_at, function () {
                return $this->started_at->diffInSeconds($this->completed_at) . ' segundos';
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}