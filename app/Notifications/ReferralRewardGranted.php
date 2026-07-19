<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Bônus de indicação concedido no ciclo novo (task-19, seção 2;
 * task-16). $count porque mais de uma indicação qualificada pode ser
 * concedida no mesmo ciclo.
 */
class ReferralRewardGranted extends Notification
{
    public function __construct(public int $count) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->count === 1 ? '1 lavagem bônus' : "{$this->count} lavagens bônus";

        return (new MailMessage)
            ->subject('Você ganhou lavagens de indicação — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você ganhou {$label} no seu ciclo atual por indicar amigos.")
            ->action('Ver minhas indicações', route('referrals.index'));
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->count === 1 ? '1 lavagem bônus' : "{$this->count} lavagens bônus";

        return [
            'title' => 'Você ganhou lavagens de indicação',
            'body' => $label,
            'url' => route('referrals.index'),
        ];
    }
}
