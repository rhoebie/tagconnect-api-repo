<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'emergency_type' => ['required', 'in:General,Medical,Fire,Crime'],
            'for_whom' => ['required', 'in:Myself,Another_Person'],
            'description' => ['required', 'string'],
            'casualties' => ['required', 'boolean'],
            'location' => ['required', 'array'],
            'location.latitude' => ['required', 'numeric'],
            'location.longitude' => ['required', 'numeric'],
            'visibility' => ['required', 'in:Private,Public'],
            'image' => ['nullable', new ValidImage],
        ];
    }

}