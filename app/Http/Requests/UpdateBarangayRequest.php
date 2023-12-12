<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use App\Rules\ValidPolygon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangayRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        if ($request->method() === 'PUT') {
            return [
                'moderator_id' => ['required', 'exists:users,id'],
                'name' => ['required', 'max:255', 'string'],
                'district' => ['nullable', 'integer'],
                'contact' => ['required', 'max:255', 'string'],
                'address' => ['required', 'max:255', 'string'],
                'location' => ['required', 'array'],
                'location.latitude' => ['required', 'numeric'],
                'location.longitude' => ['required', 'numeric'],
                'image' => ['nullable', new ValidImage]
            ];
        } else {
            return [
                //'moderator_id' => ['sometimes', 'exists:users,id'],
                'name' => ['sometimes', 'max:255', 'string'],
                'district' => ['nullable', 'integer'],
                'contact' => ['sometimes', 'max:255', 'string'],
                'address' => ['sometimes', 'max:255', 'string'],
                // 'location' => ['sometimes', 'array'],
                // 'location.latitude' => ['sometimes', 'numeric'],
                // 'location.longitude' => ['sometimes', 'numeric'],
                // 'image' => ['nullable', new ValidImage]
            ];
        }
    }
}