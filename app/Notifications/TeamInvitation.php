<?php

namespace App\Notifications;

use App\Models\CarWashInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Convite de equipe pra e-mail ainda sem cadastro (task-5, seção 6) —
 * enviado on-demand (Notification::route), o user ainda não existe.
 */
class TeamInvitation extends Notification
{
    public function __construct(public CarWashInvitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Convite pra equipe do {$this->invitation->carWash->name} — Celke Wash Club")
            ->greeting('Olá!')
            ->line("Você foi convidado pra fazer parte da equipe do lava-rápido \"{$this->invitation->carWash->name}\".")
            ->action('Aceitar convite', route('invitations.show', $this->invitation->token))
            ->line('O convite vale por 7 dias.');
    }
}
