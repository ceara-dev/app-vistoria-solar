<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Telefone brasileiro com DDD: fixo com 10 dígitos ou celular com 11 (o 9 depois do DDD).
 * Espera só os dígitos.
 */
class PhoneBr implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (preg_match('/^[1-9][1-9](9\d{8}|[2-8]\d{7})$/', (string) $value) !== 1) {
            $fail('validation.phone_br')->translate();
        }
    }
}
