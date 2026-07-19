<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Repasse marcado como pago pelo admin (task-19, seção 2) — avisa o
 * dono do lava-rápido.
 */
class PayoutPaid extends Notification
{
    public function __construct(public Payout $payout) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->payout->total_amount_cents / 100, 2, ',', '.');

        return (new MailMessage)
            ->subject('Repasse pago — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O repasse de R$ {$amount} referente ao período de {$this->payout->period_start->format('d/m/Y')} a {$this->payout->period_end->format('d/m/Y')} foi pago.")
            ->line("Referência: {$this->payout->payment_reference}.")
            ->action('Ver detalhes', route('panel.payouts.show', $this->payout));
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->payout->total_amount_cents / 100, 2, ',', '.');

        return [
            'title' => 'Repasse pago',
            'body' => "R$ {$amount}",
            'url' => route('panel.payouts.show', $this->payout),
        ];
    }
}
