<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Assinatura confirmada após o primeiro pagamento (task-7, seção 7).
 */
class SubscriptionConfirmed extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assinatura confirmada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua assinatura do plano \"{$this->subscription->plan->name}\" está confirmada.")
            ->line("Renovação em {$this->subscription->current_period_end->format('d/m/Y')}.")
            ->action('Ver minha assinatura', route('subscription.show'));
    }
}
