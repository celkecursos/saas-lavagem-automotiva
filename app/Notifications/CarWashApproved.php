<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Cadastro do lava-rápido aprovado pelo admin (task-5, seções 4 e 8).
 */
class CarWashApproved extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cadastro aprovado — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O cadastro do lava-rápido \"{$this->carWash->name}\" foi aprovado!")
            ->line('Agora você já pode ativar os produtos (clube de lavagem e/ou estacionamento) no painel.')
            ->action('Acessar o painel', route('panel.dashboard'));
    }
}
