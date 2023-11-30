<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public $baseUrl = 'http://localhost:8000';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->baseUrl . $this->image;
        return [
            'id' => $this->id,
            'role_id' => $this->roles->name,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'age' => $this->age,
            'birthdate' => $this->birthdate ? $this->birthdate->format('Y-m-d') : null,
            'contactnumber' => $this->contactnumber,
            'address' => $this->address,
            'email' => $this->email,
            'image' => $imageUrl,
            'status' => $this->status
        ];
    }
}