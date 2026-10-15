<?php

namespace App\Tenancy;

/**
 * Permissões dentro de uma empresa. Espelhadas em resources/js/Constants/permissions.ts.
 */
enum TenantPermission: string
{
    case MembersView = 'members.view';
    case MembersManage = 'members.manage';
    case CustomersView = 'customers.view';
    case CustomersManage = 'customers.manage';
    case TripsRecord = 'trips.record';
}
