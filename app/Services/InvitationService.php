<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\TenantInvitationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Tenancy\InvitationAcceptanceMode;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Convites por e-mail para entrar em uma empresa.
 *
 * O token só existe em texto no e-mail. O banco guarda o hash SHA-256.
 */
class InvitationService
{
    public const EXPIRATION_DAYS = 7;

    public function __construct(
        protected TenantInvitationRepositoryInterface $invitations,
        protected MemberRepositoryInterface $members,
        protected UserRepositoryInterface $users,
    ) {}

    /**
     * @return Collection<int, TenantInvitation>
     */
    public function pending(): Collection
    {
        return $this->invitations->pending();
    }

    /**
     * Convida o e-mail para a empresa atual ou reenvia o convite pendente.
     *
     * @throws ValidationException
     */
    public function invite(Tenant $tenant, User $inviter, string $email, TenantRole $role): TenantInvitation
    {
        if ($role === TenantRole::Owner && $this->members->roleOf($tenant, $inviter) !== TenantRole::Owner) {
            throw ValidationException::withMessages(['role' => 'Só um proprietário pode convidar proprietários.']);
        }

        return $this->send($tenant, $inviter, $email, $role);
    }

    /**
     * Convite de proprietário de uma empresa criada pela Central. Quem convida
     * é a equipe do SaaS, que não é membro, então a checagem de papel do
     * `invite()` não se aplica.
     */
    public function inviteOwner(Tenant $tenant, User $inviter, string $email): TenantInvitation
    {
        return $this->send($tenant, $inviter, $email, TenantRole::Owner);
    }

    /**
     * @throws ValidationException
     */
    private function send(Tenant $tenant, User $inviter, string $email, TenantRole $role): TenantInvitation
    {
        if ($this->members->hasActiveMemberWithEmail($tenant, $email)) {
            throw ValidationException::withMessages(['email' => 'Esta pessoa já faz parte da empresa.']);
        }

        $token = Str::random(40);

        $invitation = $this->invitations->createOrRenew(
            $email,
            $role,
            $this->hashToken($token),
            now()->addDays(self::EXPIRATION_DAYS),
            $inviter,
        );

        Notification::route('mail', $email)->notify(
            new TenantInvitationNotification($tenant->name, $inviter->name, $role, $token),
        );

        return $invitation;
    }

    public function revoke(TenantInvitation $invitation): void
    {
        $this->invitations->delete($invitation);
    }

    public function findPendingByToken(string $token): ?TenantInvitation
    {
        return $this->invitations->findPendingByTokenHash($this->hashToken($token));
    }

    public function acceptanceModeFor(TenantInvitation $invitation, ?User $user): InvitationAcceptanceMode
    {
        if ($user !== null) {
            return $this->isInvitedUser($invitation, $user)
                ? InvitationAcceptanceMode::Accept
                : InvitationAcceptanceMode::WrongAccount;
        }

        return $this->users->findByEmail($invitation->email) === null
            ? InvitationAcceptanceMode::CreateAccount
            : InvitationAcceptanceMode::Login;
    }

    /**
     * @throws ValidationException
     */
    public function accept(TenantInvitation $invitation, User $user): void
    {
        if (! $this->isInvitedUser($invitation, $user)) {
            throw ValidationException::withMessages(['invitation' => 'Este convite foi enviado para outro e-mail.']);
        }

        DB::transaction(function () use ($invitation, $user): void {
            $this->members->activate($invitation->tenant, $user);
            $this->members->assignRole($invitation->tenant, $user, $invitation->role);
            $this->users->setCurrentTenant($user, $invitation->tenant);
            $this->invitations->markAccepted($invitation);
        });
    }

    /**
     * Cria a conta de quem ainda não tem uma e aceita o convite. O e-mail já
     * chega verificado, porque o link só foi recebido nele.
     *
     * @param  array{name: string, password: string}  $data
     *
     * @throws ValidationException
     */
    public function acceptWithNewAccount(TenantInvitation $invitation, array $data): User
    {
        if ($this->users->findByEmail($invitation->email) !== null) {
            throw ValidationException::withMessages(['invitation' => 'Já existe uma conta com este e-mail. Entre para aceitar o convite.']);
        }

        return DB::transaction(function () use ($invitation, $data): User {
            $user = $this->users->create(
                ['name' => $data['name'], 'email' => $invitation->email, 'password' => $data['password']],
                emailVerified: true,
            );

            $this->accept($invitation, $user);

            return $user;
        });
    }

    private function isInvitedUser(TenantInvitation $invitation, User $user): bool
    {
        return Str::lower($user->email) === Str::lower($invitation->email);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
