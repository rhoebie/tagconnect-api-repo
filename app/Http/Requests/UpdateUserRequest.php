<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $method = $this->method();
        if ($method == 'PUT') {
            return [
                'firstname' => ['required', 'max:255', 'string'],
                'middlename' => ['nullable', 'max:255', 'string'],
                'lastname' => ['required', 'max:255', 'string'],
                'age' => ['required', 'max:255'],
                'birthdate' => ['required', 'date'],
                'contactnumber' => ['required', 'max:255', 'string'],
                'address' => ['required', 'max:255', 'string'],
                'email' => ['sometimes', 'email'],
                'password' => ['sometimes'],
                'image' => ['required', new ValidImage]
            ];
        } else {
            return [
                'firstname' => ['sometimes', 'max:255', 'string'],
                'middlename' => ['sometimes', 'nullable', 'max:255', 'string'],
                'lastname' => ['sometimes', 'max:255', 'string'],
                'age' => ['sometimes', 'max:255'],
                'birthdate' => ['sometimes', 'date'],
                'contactnumber' => ['sometimes', 'max:255', 'string'],
                'address' => ['sometimes', 'max:255', 'string'],
                'email' => ['sometimes', 'email'],
                'password' => ['sometimes'],
                'image' => ['nullable', new ValidImage]
            ];
        }
    }
}