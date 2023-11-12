<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use App\Rules\ValidPolygon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBarangayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'moderator_id' => ['sometimes', 'exists:users,id'],
            'name' => ['required', 'max:255', 'string'],
            'district' => ['sometimes', 'integer'],
            'contact' => ['required', 'max:255', 'string'],
            'address' => ['required', 'max:255', 'string'],
            'location' => ['required', 'array'],
            'location.latitude' => ['required', 'numeric'],
            'location.longitude' => ['required', 'numeric'],
            'image' => ['nullable', new ValidImage]
        ];
    }
}