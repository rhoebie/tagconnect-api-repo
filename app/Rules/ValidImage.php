<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidImage implements Rule
{

    public function passes($attribute, $value)
    {
        // Ensure that the value is a valid base64 string
        if (!preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $value)) {
            return false;
        }

        // Attempt to decode the base64 string
        $decodedValue = base64_decode($value, true);

        // Check if decoding was successful and the result is not empty
        return !empty($decodedValue);
    }

    public function message()
    {
        return 'The :attribute must be a valid base64-encoded string.';
    }
}