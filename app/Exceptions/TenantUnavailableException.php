<?php

namespace App\Exceptions;

use App\Models\Tenant;
use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

/**
 * A empresa existe e a pessoa é membro, mas ela está suspensa, cancelada ou
 * com o teste vencido. O token não é revogado: o acesso pode voltar.
 */
class TenantUnavailableException extends Exception implements ShouldntReport
{
    public function __construct(public Tenant $tenant)
    {
        parent::__construct($tenant->blockedReason() ?? 'Esta empresa está sem acesso.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 423);
    }
}
