<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Cadastro do lava-rápido rejeitado, com o motivo (task-5, seções 4 e 8).
 */
class CarWashRejected extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cadastro não aprovado — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}.")
            ->line("O cadastro do lava-rápido \"{$this->carWash->name}\" não foi aprovado.")
            ->line("Motivo: {$this->carWash->rejection_reason}")
            ->line('Você pode corrigir os dados no painel e reenviar o cadastro para uma nova análise.')
            ->action('Corrigir cadastro', route('panel.dashboard'));
    }
}
