<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Lava-rápido suspenso pelo admin (task-5, seções 4 e 8).
 */
class CarWashSuspended extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cadastro suspenso — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}.")
            ->line("O lava-rápido \"{$this->carWash->name}\" foi suspenso pela plataforma.")
            ->line('Enquanto suspenso, ele não aparece para os assinantes nem pode operar.')
            ->line('Entre em contato com o suporte para mais detalhes.');
    }
}
