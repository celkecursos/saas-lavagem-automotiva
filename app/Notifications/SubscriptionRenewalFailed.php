<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Falha na cobrança de renovação — avisa antes de cancelar (task-7,
 * seções 4 e 7).
 */
class SubscriptionRenewalFailed extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Não conseguimos cobrar sua assinatura — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}.")
            ->line("Não conseguimos cobrar a renovação do plano \"{$this->subscription->plan->name}\".")
            ->line('Atualize seu cartão nos próximos dias pra evitar o cancelamento automático da assinatura.');
    }
}
