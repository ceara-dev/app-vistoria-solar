<?php

namespace App\Exceptions;

use App\Http\Resources\TenantSummaryResource;
use App\Models\Tenant;
use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

/**
 * A pessoa participa de mais de uma empresa e não disse em qual quer entrar.
 * O app mostra a lista e repete o login com `tenant_id`.
 */
class TenantSelectionRequiredException extends Exception implements ShouldntReport
{
    /**
     * @param  Collection<int, Tenant>  $tenants
     */
    public function __construct(public Collection $tenants)
    {
        parent::__construct('Escolha a empresa em que você vai trabalhar.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'tenants' => TenantSummaryResource::collection($this->tenants)->resolve(),
        ], 409);
    }
}
