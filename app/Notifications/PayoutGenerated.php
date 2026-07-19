<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Payout gerado — avisa o dono do lava-rápido (task-9, seção 5).
 */
class PayoutGenerated extends Notification
{
    public function __construct(public Payout $payout) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->payout->total_amount_cents / 100, 2, ',', '.');

        return (new MailMessage)
            ->subject('Você tem um repasse a receber — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você tem R$ {$amount} a receber referente ao período de {$this->payout->period_start->format('d/m/Y')} a {$this->payout->period_end->format('d/m/Y')}.")
            ->action('Ver detalhes', route('panel.payouts.index'));
    }
}
