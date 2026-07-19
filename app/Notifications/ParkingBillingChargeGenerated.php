<?php

namespace App\Notifications;

use App\Models\ParkingBillingCharge;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Cobrança de estacionamento não-gratuita gerada — o lava-rápido
 * precisa pagar a plataforma (task-10, seção 7).
 */
class ParkingBillingChargeGenerated extends Notification
{
    public function __construct(public ParkingBillingCharge $charge) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->charge->fee_amount_cents / 100, 2, ',', '.');

        return (new MailMessage)
            ->subject('Cobrança do estacionamento — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O volume de lavagens do período não atingiu a capacidade do seu estacionamento, então há uma taxa de R$ {$amount} a pagar.")
            ->action('Pagar agora', route('panel.parking.charges.index'));
    }
}
