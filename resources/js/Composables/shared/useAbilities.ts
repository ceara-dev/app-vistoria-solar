import { usePage } from '@inertiajs/vue3';
import type { Permission } from '@/Constants';

/**
 * Permissões do usuário na empresa atual, vindas das props compartilhadas.
 * Servem só para esconder ações: quem decide é a policy no backend.
 */
export function useAbilities() {
    const page = usePage();

    function can(permission: Permission): boolean {
        return page.props.can.includes(permission);
    }

    return { can };
}
