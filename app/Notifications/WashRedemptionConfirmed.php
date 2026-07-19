<?php

namespace App\Notifications;

use App\Models\WashRedemption;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Lavagem confirmada pelo funcionário (task-19, seção 2) — avisa o
 * assinante que a cota foi debitada.
 */
class WashRedemptionConfirmed extends Notification
{
    public function __construct(public WashRedemption $redemption) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Lavagem confirmada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua lavagem em \"{$this->redemption->carWash->name}\" foi confirmada.")
            ->action('Ver histórico', route('wash.choose'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Lavagem confirmada',
            'body' => $this->redemption->carWash->name,
            'url' => route('wash.choose'),
        ];
    }
}
