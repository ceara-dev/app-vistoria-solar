<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Sessão do app: quem é, em qual empresa e com quais permissões.
 * O token em texto só existe na resposta do login.
 */
final readonly class ApiSession
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public User $user,
        public Tenant $tenant,
        public array $permissions,
        public ?string $plainTextToken = null,
        public ?CarbonInterface $expiresAt = null,
    ) {}
}
