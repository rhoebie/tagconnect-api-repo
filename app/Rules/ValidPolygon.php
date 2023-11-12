<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class ValidPolygon implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        // Check if the value is an array
        if (!is_array($value)) {
            return false;
        }

        // Check if the array has at least three points
        if (count($value) < 3) {
            return false;
        }

        // Check if each element of the array is a valid point (an array of two floats)
        foreach ($value as $point) {
            if (!is_array($point) || count($point) !== 2 || !is_numeric($point[0]) || !is_numeric($point[1])) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must be a valid polygon JSON array.';
    }
}