<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function create(array $attributes, bool $emailVerified = false): User
    {
        $user = new User($attributes);
        $user->forceFill(['email_verified_at' => $emailVerified ? now() : null])->save();

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function setCurrentTenant(User $user, ?Tenant $tenant): void
    {
        $user->forceFill(['current_tenant_id' => $tenant?->id])->save();
        $user->setRelation('currentTenant', $tenant);
    }

    public function issueToken(User $user, Tenant $tenant, string $deviceName, CarbonInterface $expiresAt): NewAccessToken
    {
        return DB::transaction(function () use ($user, $tenant, $deviceName, $expiresAt): NewAccessToken {
            $newToken = $user->createToken($deviceName, ['*'], $expiresAt);
            $newToken->accessToken->forceFill(['tenant_id' => $tenant->id])->save();

            return $newToken;
        });
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
