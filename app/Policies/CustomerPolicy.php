<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantPermission;

class CustomerPolicy
{
    public function __construct(private TenantContext $tenantContext) {}

    public function viewAny(User $user): bool
    {
        return $user->can(TenantPermission::CustomersView->value);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->tenantContext->owns($customer) && $user->can(TenantPermission::CustomersView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(TenantPermission::CustomersManage->value);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->tenantContext->owns($customer) && $user->can(TenantPermission::CustomersManage->value);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->tenantContext->owns($customer) && $user->can(TenantPermission::CustomersManage->value);
    }
}
