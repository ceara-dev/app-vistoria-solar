import { router } from '@inertiajs/vue3';
import { MESSAGES, ROUTE_NAMES_TENANT } from '@/Constants';
import type { Customer } from '@/Types/tenant/customer';

export function useCustomerActions() {
    function deleteCustomer(customer: Customer): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_DELETE_CUSTOMER)) {
            return;
        }

        router.delete(route(ROUTE_NAMES_TENANT.CUSTOMERS.DESTROY, customer.id));
    }

    return { deleteCustomer };
}
