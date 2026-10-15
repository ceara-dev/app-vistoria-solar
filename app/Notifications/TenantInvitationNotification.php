<?php

namespace App\Notifications;

use App\Services\InvitationService;
use App\Tenancy\TenantRole;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail com o link do convite. Não vai para a fila de propósito: o token em
 * texto ficaria gravado no payload do job.
 */
class TenantInvitationNotification extends Notification
{
    public function __construct(
        public string $tenantName,
        public string $inviterName,
        public TenantRole $role,
        public string $token,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Convite para a empresa {$this->tenantName}")
            ->greeting('Olá!')
            ->line("{$this->inviterName} convidou você para entrar na empresa {$this->tenantName} no Vistoria Solar, como {$this->role->label()}.")
            ->action('Aceitar convite', $this->acceptUrl())
            ->line('O convite vale por '.InvitationService::EXPIRATION_DAYS.' dias. Se você não esperava este e-mail, pode ignorá-lo.');
    }

    public function acceptUrl(): string
    {
        return route('invitations.show', $this->token);
    }
}
