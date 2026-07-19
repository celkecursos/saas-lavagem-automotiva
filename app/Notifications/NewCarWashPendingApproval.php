<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Novo cadastro de lava-rápido entrou na fila de aprovação (task-19,
 * seção 2) — pros admins com a permission car-washes.approve.
 */
class NewCarWashPendingApproval extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo lava-rápido aguardando aprovação — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("\"{$this->carWash->name}\" acabou de se cadastrar e está aguardando aprovação.")
            ->action('Ver cadastro', route('car-washes.show', $this->carWash));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Novo lava-rápido aguardando aprovação',
            'body' => $this->carWash->name,
            'url' => route('car-washes.show', $this->carWash),
        ];
    }
}
