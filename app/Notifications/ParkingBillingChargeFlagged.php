<?php

namespace App\Notifications;

use App\Models\ParkingBillingCharge;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Cobrança de estacionamento sinalizada pelo antifraude (task-19,
 * seção 2) — pros admins com a permission parking-billing-charges.index.
 */
class ParkingBillingChargeFlagged extends Notification
{
    public function __construct(public ParkingBillingCharge $charge) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cobrança de estacionamento sinalizada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A cobrança de \"{$this->charge->carWash->name}\" referente a {$this->charge->period_start->format('d/m/Y')}–{$this->charge->period_end->format('d/m/Y')} foi sinalizada pelo antifraude e precisa de revisão manual.")
            ->action('Revisar cobrança', route('parking-billing-charges.show', $this->charge));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cobrança de estacionamento sinalizada',
            'body' => $this->charge->carWash->name,
            'url' => route('parking-billing-charges.show', $this->charge),
        ];
    }
}
