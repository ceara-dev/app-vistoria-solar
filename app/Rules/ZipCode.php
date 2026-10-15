<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * CEP com 8 dígitos. Espera só os dígitos.
 */
class ZipCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (preg_match('/^\d{8}$/', (string) $value) !== 1 || (string) $value === '00000000') {
            $fail('validation.zip_code')->translate();
        }
    }
}
