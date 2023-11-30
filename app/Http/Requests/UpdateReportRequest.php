<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
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
                'user_id' => ['required', 'exists:users,id'],
                'barangay_id' => ['required', 'exists:barangays,id'],
                'emergency_type' => ['required', 'in:General,Medical,Fire,Crime'],
                'for_whom' => ['required', 'in:Myself,Another_Person'],
                'description' => ['required', 'string'],
                'casualties' => ['required', 'boolean'],
                'location' => ['required', 'array'],
                'location.latitude' => ['required', 'numeric'],
                'location.longitude' => ['required', 'numeric'],
                'visibility' => ['required', 'in:Private,Public'],
                'image' => ['nullable', new ValidImage],
                'status' => ['required', 'in:Submitted,Processing,Resolved'],
            ];
        } else {
            return [
                'user_id' => ['sometimes', 'exists:users,id'],
                'barangay_id' => ['sometimes', 'exists:barangays,id'],
                'emergency_type' => ['sometimes', 'in:General,Medical,Fire,Crime'],
                'for_whom' => ['required', 'in:Myself,Another_Person'],
                'description' => ['sometimes', 'string'],
                'casualties' => ['sometimes', 'boolean'],
                'location' => ['sometimes', 'array'],
                'location.latitude' => ['sometimes', 'numeric'],
                'location.longitude' => ['sometimes', 'numeric'],
                'visibility' => ['sometimes', 'in:Private,Public'],
                'image' => ['nullable', new ValidImage],
                'status' => ['sometimes', 'in:Submitted,Processing,Resolved'],
            ];
        }
    }

}