<?php

namespace App\Tenancy;

enum TenantStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Em teste',
            self::Active => 'Ativa',
            self::Suspended => 'Suspensa',
            self::Canceled => 'Cancelada',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
