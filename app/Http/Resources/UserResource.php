<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'role_id' => $this->roles->name,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'age' => $this->age,
            'birthdate' => $this->birthdate ? $this->birthdate->format('Y-m-d') : null,
            'contactnumber' => $this->contactnumber,
            'address' => $this->address,
            'email' => $this->email,
            'image' => $this->image,
            'status' => $this->status
        ];
    }
}