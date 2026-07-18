<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Renovação automática confirmada (task-7, seção 7).
 */
class SubscriptionRenewed extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assinatura renovada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua assinatura do plano \"{$this->subscription->plan->name}\" foi renovada.")
            ->line("Próxima renovação em {$this->subscription->current_period_end->format('d/m/Y')}.");
    }
}
