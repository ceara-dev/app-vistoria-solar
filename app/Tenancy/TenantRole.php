<?php

namespace App\Tenancy;

/**
 * Papéis globais, atribuídos a cada membro por empresa (spatie com teams).
 */
enum TenantRole: string
{
    case Owner = 'proprietario';
    case Admin = 'administrador';
    case Manager = 'gestor';
    case Inspector = 'vistoriador';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Admin => 'Administrador',
            self::Manager => 'Gestor',
            self::Inspector => 'Vistoriador',
        };
    }

    /**
     * @return list<TenantPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner, self::Admin => TenantPermission::cases(),
            self::Manager => [
                TenantPermission::MembersView,
                TenantPermission::CustomersView,
                TenantPermission::CustomersManage,
                TenantPermission::TripsRecord,
            ],
            self::Inspector => [TenantPermission::CustomersView, TenantPermission::TripsRecord],
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role): array => ['value' => $role->value, 'label' => $role->label()],
            self::cases(),
        );
    }
}
