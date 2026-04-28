<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class EgyptianPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = preg_replace('/\s+/', '', (string) $value);

        // Accepts: +201012345678, 00201012345678, 01012345678
        if (! preg_match('/^(\+20|0020|0)1[0125]\d{8}$/', $normalized)) {
            $fail('The :attribute is not a valid Egyptian mobile number.');
        }

    }
}
