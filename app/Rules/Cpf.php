<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * CPF com dígitos verificadores válidos. Espera só os 11 dígitos, sem pontuação.
 */
class Cpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('validation.cpf')->translate();
        }
    }

    public static function isValid(string $cpf): bool
    {
        if (preg_match('/^\d{11}$/', $cpf) !== 1 || count(array_unique(str_split($cpf))) === 1) {
            return false;
        }

        foreach ([9, 10] as $position) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * ($position + 1 - $index);
            }

            if ((int) $cpf[$position] !== ($sum * 10) % 11 % 10) {
                return false;
            }
        }

        return true;
    }
}
