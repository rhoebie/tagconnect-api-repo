<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Use the DB::raw function to extract latitude and longitude from the POINT
        $location = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM reports WHERE id = ?", [$this->id])[0];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'barangay_id' => $this->barangay_id,
            'emergency_type' => $this->emergency_type,
            'for_whom' => $this->for_whom,
            'description' => $this->description,
            'casualties' => $this->casualties,
            'location' => [
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
            ],
            'visibility' => $this->visibility,
            'image' => getImageUrl($this->image),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}