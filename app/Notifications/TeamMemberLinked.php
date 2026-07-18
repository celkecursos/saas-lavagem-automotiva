<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso pra quem JÁ tinha conta e foi vinculado direto como employee
 * (task-5, seção 6).
 */
class TeamMemberLinked extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Você entrou pra equipe do {$this->carWash->name} — Celke Wash Club")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você foi adicionado à equipe do lava-rápido \"{$this->carWash->name}\".")
            ->action('Acessar o painel', route('panel.dashboard'));
    }
}
