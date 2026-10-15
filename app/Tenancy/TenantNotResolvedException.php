<?php

namespace App\Tenancy;

use LogicException;

/**
 * Lançada quando um dado de empresa é acessado sem empresa definida no contexto.
 */
class TenantNotResolvedException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Nenhuma empresa definida no contexto atual.');
    }
}
