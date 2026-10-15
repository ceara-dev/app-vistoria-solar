<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * CNPJ com dígitos verificadores válidos. Espera só os 14 dígitos, sem pontuação.
 */
class Cnpj implements ValidationRule
{
    private const FIRST_DIGIT_WEIGHTS = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    private const SECOND_DIGIT_WEIGHTS = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('validation.cnpj')->translate();
        }
    }

    public static function isValid(string $cnpj): bool
    {
        if (preg_match('/^\d{14}$/', $cnpj) !== 1 || count(array_unique(str_split($cnpj))) === 1) {
            return false;
        }

        foreach ([self::FIRST_DIGIT_WEIGHTS, self::SECOND_DIGIT_WEIGHTS] as $weights) {
            $position = count($weights);
            $sum = 0;

            foreach ($weights as $index => $weight) {
                $sum += (int) $cnpj[$index] * $weight;
            }

            $remainder = $sum % 11;

            if ((int) $cnpj[$position] !== ($remainder < 2 ? 0 : 11 - $remainder)) {
                return false;
            }
        }

        return true;
    }
}
