<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonInterface;
use Laravel\Sanctum\NewAccessToken;

interface UserRepositoryInterface
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function create(array $attributes, bool $emailVerified = false): User;

    public function findByEmail(string $email): ?User;

    public function setCurrentTenant(User $user, ?Tenant $tenant): void;

    /**
     * Token do app que só vale na empresa informada.
     */
    public function issueToken(User $user, Tenant $tenant, string $deviceName, CarbonInterface $expiresAt): NewAccessToken;

    public function revokeCurrentToken(User $user): void;
}
