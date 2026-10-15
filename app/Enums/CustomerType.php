<?php

namespace App\Enums;

enum CustomerType: string
{
    case Individual = 'PF';
    case Company = 'PJ';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Pessoa física',
            self::Company => 'Pessoa jurídica',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
