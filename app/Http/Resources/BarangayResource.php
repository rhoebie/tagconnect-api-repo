<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class BarangayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { // Use the DB::raw function to extract latitude and longitude from the POINT
        $location = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM barangays WHERE id = ?", [$this->id])[0];

        return [
            'id' => $this->id,
            'moderator_id' => $this->moderator_id,
            'name' => $this->name,
            'district' => $this->district,
            'contact' => $this->contact,
            'address' => $this->address,
            'location' => [
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
            ],
            'image' => $this->image
        ];
    }
}