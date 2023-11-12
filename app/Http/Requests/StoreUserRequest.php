<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'role_id' => ['required', 'exists:roles,id'],
            'firstname' => ['nullable', 'max:255', 'string'],
            'middlename' => ['nullable', 'max:255', 'string'],
            'lastname' => ['nullable', 'max:255', 'string'],
            'age' => ['nullable', 'integer'],
            'birthdate' => ['nullable', 'date'],
            'contactnumber' => ['nullable', 'max:255', 'string'],
            'address' => ['nullable', 'max:255', 'string'],
            'email' => ['required', 'unique:users,email', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'image' => ['nullable', new ValidImage],
        ];
    }

}