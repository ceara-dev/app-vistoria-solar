<?php

namespace App\Tenancy;

/**
 * Eventos de vínculo gravados no histórico da empresa (`audits.event`).
 */
enum MembershipEvent: string
{
    case Activated = 'member_activated';
    case Deactivated = 'member_deactivated';
    case RoleChanged = 'member_role_changed';
}
