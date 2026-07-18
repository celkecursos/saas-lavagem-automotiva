<?php

namespace App\Notifications;

use App\Models\CarWashInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso pros owners quando um convite de equipe é aceito (task-5,
 * seção 8).
 */
class TeamInviteAccepted extends Notification
{
    public function __construct(public CarWashInvitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Convite de equipe aceito — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O convite enviado pra {$this->invitation->email} foi aceito — a pessoa já faz parte da equipe do \"{$this->invitation->carWash->name}\".");
    }
}
